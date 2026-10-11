<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Lsp;
use App\Models\Trainer;
use App\Models\Vendor;
use App\Models\VendorBill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VendorBillService
{
    /**
     * Buat draft tagihan vendor / LSP baru.
     *
     * @param array{
     *     company_id: int,
     *     lsp_id?: int|null,
     *     vendor_id?: int|null,
     *     trainer_id?: int|null,
     *     batch_id?: int|null,
     *     bill_date: string,
     *     due_date: string,
     *     vendor_invoice_number?: string|null,
     *     tax_amount?: float|null,
     *     notes?: string|null,
     *     items: array<array{
     *         account_id: int,
     *         description: string,
     *         quantity: int,
     *         unit_price: float
     *     }>
     * } $data
     */
    public function createBill(array $data): VendorBill
    {
        return DB::transaction(function () use ($data) {
            $companyId = $data['company_id'];
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new InvalidArgumentException('Tagihan wajib memiliki minimal 1 rincian item biaya.');
            }

            if (empty($data['lsp_id']) && empty($data['vendor_id']) && empty($data['trainer_id'])) {
                throw new InvalidArgumentException('Pihak penagih wajib ditentukan (pilih salah satu: LSP mitra, Vendor, atau Instruktur).');
            }

            // 1. Hitung Subtotal Item
            $subtotal = 0.0;
            $processedItems = [];

            foreach ($items as $item) {
                $qty = max(1, (int) $item['quantity']);
                $price = (float) $item['unit_price'];
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;

                $processedItems[] = [
                    'account_id' => (int) $item['account_id'],
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $lineTotal,
                ];
            }

            // 2. Pajak & Total
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $totalAmount = $subtotal + $taxAmount;

            // 3. Generate Nomor Tagihan BILL-YYYY-MM-XXXX
            $billNumber = $this->generateBillNumber($companyId, $data['bill_date']);
            $user = Auth::user();

            // 4. Buat Record VendorBill
            $bill = VendorBill::create([
                'company_id' => $companyId,
                'lsp_id' => $data['lsp_id'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'trainer_id' => $data['trainer_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
                'bill_number' => $billNumber,
                'vendor_invoice_number' => $data['vendor_invoice_number'] ?? null,
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'balance_due' => $totalAmount,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => $user?->id ?? 1,
            ]);

            // 5. Buat Item Tagihan
            foreach ($processedItems as $itemData) {
                $bill->items()->create($itemData);
            }

            // 6. Audit Trail
            AuditTrailService::log(
                'create_vendor_bill',
                $bill,
                null,
                ['bill_number' => $billNumber, 'total' => $totalAmount],
                "Pembuatan Tagihan {$billNumber} sebesar Rp ".number_format($totalAmount, 2)
            );

            return $bill;
        });
    }

    /**
     * Setujui Tagihan (Approve Bill) dan posting jurnal pengakuan beban & utang.
     */
    public function approveBill(VendorBill $bill): VendorBill
    {
        return DB::transaction(function () use ($bill) {
            if (! in_array($bill->status, ['draft', 'pending_approval'])) {
                throw new InvalidArgumentException("Tagihan {$bill->bill_number} sudah disetujui atau dibatalkan.");
            }

            $companyId = $bill->company_id;
            $user = Auth::user();

            // 1. Tentukan Akun Utang (Kredit)
            // Jika LSP: Akun 202 Utang Sertifikasi
            // Jika Vendor/Trainer: Akun 201 Utang Usaha
            if ($bill->lsp_id) {
                $accLiability = Account::where('company_id', $companyId)->where('code', '202')->first();
            } else {
                $accLiability = Account::where('company_id', $companyId)->where('code', '201')->first();
            }

            if (! $accLiability) {
                $accLiability = Account::where('company_id', $companyId)->where('code', '201')->firstOrFail();
            }

            // 2. Susun Baris Jurnal Double-Entry
            $journalLines = [];

            // Debit setiap akun beban per item tagihan
            $bill->load('items.account');
            foreach ($bill->items as $item) {
                $journalLines[] = [
                    'account_id' => $item->account_id,
                    'debit' => (float) $item->total_price,
                    'credit' => 0.00,
                    'memo' => "Beban {$item->description} (Tagihan {$bill->bill_number})",
                ];
            }

            // Debit Pajak Masukan jika ada tax_amount
            if ((float) $bill->tax_amount > 0) {
                $accTax = Account::where('company_id', $companyId)->where('code', '105')->first();
                if ($accTax) {
                    $journalLines[] = [
                        'account_id' => $accTax->id,
                        'debit' => (float) $bill->tax_amount,
                        'credit' => 0.00,
                        'memo' => "Pajak Masukan Tagihan {$bill->bill_number}",
                    ];
                }
            }

            // Kredit Akun Utang (202 atau 201) sebesar total_amount
            $payeeName = $bill->lsp?->name ?? $bill->vendor?->name ?? $bill->trainer?->name ?? 'Vendor';
            $journalLines[] = [
                'account_id' => $accLiability->id,
                'debit' => 0.00,
                'credit' => (float) $bill->total_amount,
                'memo' => "Pengakuan Utang {$bill->bill_number} - {$payeeName}",
            ];

            // 3. Posting Jurnal via JournalPostingService
            $postingService = new JournalPostingService;
            $journal = $postingService->createAndPost([
                'company_id' => $companyId,
                'entry_date' => $bill->bill_date->toDateString(),
                'batch_id' => $bill->batch_id,
                'source_module' => 'vendor_bill',
                'reference' => $bill,
                'description' => "Pengakuan Beban & Utang Tagihan {$bill->bill_number} ({$payeeName})",
                'lines' => $journalLines,
            ]);

            // 4. Update Status Tagihan
            $bill->update([
                'status' => 'approved',
                'journal_entry_id' => $journal->id,
                'approved_by' => $user?->id,
                'approved_at' => now(),
            ]);

            // 5. Audit Trail
            AuditTrailService::log(
                'approve_vendor_bill',
                $bill,
                ['status' => 'draft'],
                ['status' => 'approved', 'journal_id' => $journal->id],
                "Persetujuan tagihan {$bill->bill_number} dan posting jurnal {$journal->entry_number}"
            );

            return $bill;
        });
    }

    /**
     * Generate Nomor Tagihan BILL-YYYY-MM-XXXX
     */
    protected function generateBillNumber(int $companyId, string $date): string
    {
        $yearMonth = date('Y-m', strtotime($date));
        $prefix = 'BILL-'.$yearMonth.'-';

        $lastNumber = VendorBill::where('company_id', $companyId)
            ->where('bill_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('bill_number');

        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
