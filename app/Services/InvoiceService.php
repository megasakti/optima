<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceService
{
    /**
     * Buat draft invoice baru beserta rincian item.
     *
     * @param array{
     *     company_id: int,
     *     customer_id: int,
     *     program_id?: int|null,
     *     batch_id?: int|null,
     *     invoice_date: string,
     *     due_date: string,
     *     po_number?: string|null,
     *     tax_ppn_rate?: float|null,
     *     tax_pph23_rate?: float|null,
     *     down_payment_applied?: float|null,
     *     notes?: string|null,
     *     terms_conditions?: string|null,
     *     items: array<array{
     *         item_type: string,
     *         description: string,
     *         quantity: int,
     *         unit_price: float,
     *         discount?: float|null,
     *         account_id?: int|null
     *     }>
     * } $data
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $companyId = $data['company_id'];
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new InvalidArgumentException('Invoice wajib memiliki minimal 1 item jasa pelatihan atau sertifikasi.');
            }

            // 1. Hitung Subtotal dari rincian item
            $subtotal = 0.0;
            $processedItems = [];

            foreach ($items as $item) {
                $qty = max(1, (int) $item['quantity']);
                $price = (float) $item['unit_price'];
                $discount = (float) ($item['discount'] ?? 0);
                $totalPrice = ($qty * $price) - $discount;

                $subtotal += $totalPrice;

                $processedItems[] = [
                    'item_type' => $item['item_type'] ?? 'training',
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                    'account_id' => $item['account_id'] ?? null,
                ];
            }

            // 2. Hitung Pajak PPN & Potongan PPh 23
            $ppnRate = (float) ($data['tax_ppn_rate'] ?? 0);
            $ppnAmount = ($ppnRate > 0) ? ($subtotal * ($ppnRate / 100)) : 0.0;

            $pph23Rate = (float) ($data['tax_pph23_rate'] ?? 0);
            $pph23Amount = ($pph23Rate > 0) ? ($subtotal * ($pph23Rate / 100)) : 0.0;

            // 3. Potongan Uang Muka (Down Payment Applied)
            $dpApplied = (float) ($data['down_payment_applied'] ?? 0);

            // Total Amount = Subtotal + PPN - Potongan DP
            $totalAmount = max(0, ($subtotal + $ppnAmount) - $dpApplied);

            // 4. Generate Nomor Invoice Unik
            $invoiceNumber = $this->generateInvoiceNumber($companyId, $data['invoice_date']);

            // 5. Buat Record Invoice
            $user = Auth::user();
            $invoice = Invoice::create([
                'company_id' => $companyId,
                'customer_id' => $data['customer_id'],
                'program_id' => $data['program_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'po_number' => $data['po_number'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'tax_ppn_rate' => $ppnRate,
                'tax_ppn_amount' => $ppnAmount,
                'tax_pph23_rate' => $pph23Rate,
                'tax_pph23_amount' => $pph23Amount,
                'down_payment_applied' => $dpApplied,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'balance_due' => $totalAmount,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? 'Pembayaran transfer ke Rekening BCA Operasional 8801239988 a.n PT Optima Mitra Sertifikasi',
                'created_by' => $user?->id ?? 1,
            ]);

            // 6. Buat Invoice Items
            foreach ($processedItems as $itemData) {
                $invoice->items()->create($itemData);
            }

            // 7. Audit Log
            AuditTrailService::log(
                'create_invoice',
                $invoice,
                null,
                ['invoice_number' => $invoice->invoice_number, 'total' => $totalAmount],
                "Pembuatan Invoice {$invoice->invoice_number} senilai Rp ".number_format($totalAmount, 2)
            );

            return $invoice;
        });
    }

    /**
     * Terbitkan Invoice resmi (Issue Invoice) dan posting jurnal piutang otomatis.
     */
    public function issueInvoice(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            if (! in_array($invoice->status, ['draft', 'pending_approval'])) {
                throw new InvalidArgumentException("Invoice {$invoice->invoice_number} sudah diterbitkan atau dibatalkan.");
            }

            $companyId = $invoice->company_id;
            $user = Auth::user();

            // 1. Akun COA Terkait
            $accAR = Account::where('company_id', $companyId)->where('code', '103')->first(); // Piutang Usaha
            $accRevenue = Account::where('company_id', $companyId)->where('code', '401')->first(); // Pendapatan Pelatihan
            $accDP = Account::where('company_id', $companyId)->where('code', '205')->first(); // Pendapatan Diterima di Muka (Kewajiban)
            $accTaxPayable = Account::where('company_id', $companyId)->where('code', '204')->first(); // Utang PPN

            if (! $accAR || ! $accRevenue) {
                throw new InvalidArgumentException('Akun Piutang Usaha (103) atau Pendapatan Pelatihan (401) tidak ditemukan.');
            }

            // 2. Susun Baris Jurnal Double-Entry
            $journalLines = [];

            // Debit Piutang Usaha (sebesar total tagihan yang harus dibayar klien)
            $journalLines[] = [
                'account_id' => $accAR->id,
                'debit' => $invoice->total_amount,
                'credit' => 0.00,
                'memo' => "Piutang Invoice {$invoice->invoice_number} - {$invoice->customer->name}",
            ];

            // Jika ada Uang Muka (DP) yang dipotong: Debit Pendapatan Diterima di Muka (205)
            if ((float) $invoice->down_payment_applied > 0 && $accDP) {
                $journalLines[] = [
                    'account_id' => $accDP->id,
                    'debit' => $invoice->down_payment_applied,
                    'credit' => 0.00,
                    'memo' => "Realokasi Uang Muka DP Klien Invoice {$invoice->invoice_number}",
                ];
            }

            // Kredit Pendapatan Pelatihan (401) sebesar subtotal
            $journalLines[] = [
                'account_id' => $accRevenue->id,
                'debit' => 0.00,
                'credit' => $invoice->subtotal,
                'memo' => "Pendapatan Jasa Pelatihan Invoice {$invoice->invoice_number}",
            ];

            // Jika ada PPN Keluaran: Kredit Utang Pajak PPN (204)
            if ((float) $invoice->tax_ppn_amount > 0 && $accTaxPayable) {
                $journalLines[] = [
                    'account_id' => $accTaxPayable->id,
                    'debit' => 0.00,
                    'credit' => $invoice->tax_ppn_amount,
                    'memo' => "Utang PPN Keluaran Invoice {$invoice->invoice_number}",
                ];
            }

            // 3. Posting Jurnal via JournalPostingService
            $postingService = new JournalPostingService;
            $journal = $postingService->createAndPost([
                'company_id' => $companyId,
                'entry_date' => $invoice->invoice_date->toDateString(),
                'batch_id' => $invoice->batch_id,
                'source_module' => 'invoice',
                'reference' => $invoice,
                'description' => "Pengakuan Piutang Invoice {$invoice->invoice_number} - {$invoice->customer->name}",
                'lines' => $journalLines,
            ]);

            // 4. Update Status Invoice
            $invoice->update([
                'status' => 'issued',
                'journal_entry_id' => $journal->id,
                'approved_by' => $user?->id,
                'approved_at' => now(),
            ]);

            // 5. Audit Trail
            AuditTrailService::log(
                'issue_invoice',
                $invoice,
                ['status' => 'draft'],
                ['status' => 'issued', 'journal_id' => $journal->id],
                "Penerbitan Invoice {$invoice->invoice_number} dan posting jurnal {$journal->entry_number}"
            );

            return $invoice;
        });
    }

    /**
     * Generate Nomor Invoice INV-YYYY-MM-XXXX
     */
    protected function generateInvoiceNumber(int $companyId, string $date): string
    {
        $yearMonth = date('Y-m', strtotime($date));
        $prefix = 'INV-'.$yearMonth.'-';

        $lastNumber = Invoice::where('company_id', $companyId)
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('invoice_number');

        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
