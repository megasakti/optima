<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\CashBankAccount;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use App\Models\VendorPaymentAllocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VendorPaymentService
{
    /**
     * Catat pembayaran utang ke vendor/LSP dan alokasikan ke tagihan.
     *
     * @param array{
     *     company_id: int,
     *     lsp_id?: int|null,
     *     vendor_id?: int|null,
     *     trainer_id?: int|null,
     *     cash_bank_account_id: int,
     *     payment_date: string,
     *     payment_method: string,
     *     amount: float,
     *     reference_number?: string|null,
     *     notes?: string|null,
     *     allocations: array<array{vendor_bill_id: int, amount_applied: float}>
     * } $data
     */
    public function recordPayment(array $data): VendorPayment
    {
        return DB::transaction(function () use ($data) {
            $companyId = $data['company_id'];
            $amount = (float) $data['amount'];
            $allocations = $data['allocations'] ?? [];

            if (empty($allocations)) {
                throw new InvalidArgumentException('Pembayaran wajib dialokasikan ke minimal 1 tagihan biaya.');
            }

            // 1. Validasi Total Alokasi
            $sumAllocated = 0.0;
            foreach ($allocations as $alloc) {
                $sumAllocated += (float) $alloc['amount_applied'];
            }

            if (bccomp((string) $sumAllocated, (string) $amount, 2) !== 0) {
                throw new InvalidArgumentException(
                    'Total alokasi pelunasan (Rp '.number_format($sumAllocated, 2).') harus sama dengan nominal pembayaran (Rp '.number_format($amount, 2).').'
                );
            }

            // 2. Validasi Sisa Tagihan Tiap Bill (Pessimistic Locking)
            $isLspPayment = false;
            $firstBatchId = null;

            foreach ($allocations as $alloc) {
                $bill = VendorBill::where('company_id', $companyId)->lockForUpdate()->findOrFail($alloc['vendor_bill_id']);
                $applyAmt = (float) $alloc['amount_applied'];

                if ($applyAmt > (float) $bill->balance_due + 0.01) {
                    throw new InvalidArgumentException(
                        'Alokasi pembayaran Rp '.number_format($applyAmt, 2)." melebihi sisa tagihan {$bill->bill_number} (Rp ".number_format($bill->balance_due, 2).').'
                    );
                }

                if ($bill->lsp_id) {
                    $isLspPayment = true;
                }
                if (! $firstBatchId && $bill->batch_id) {
                    $firstBatchId = $bill->batch_id;
                }
            }

            // 3. Validasi Saldo Kas/Bank Cukup
            $cashBank = CashBankAccount::where('company_id', $companyId)->lockForUpdate()->findOrFail($data['cash_bank_account_id']);
            if ((float) $cashBank->current_balance < $amount) {
                throw new InvalidArgumentException(
                    "Saldo rekening {$cashBank->name} (Rp ".number_format($cashBank->current_balance, 2).') tidak mencukupi untuk melakukan pembayaran Rp '.number_format($amount, 2).'.'
                );
            }

            // 4. Generate Nomor Voucher VPY-YYYY-MM-XXXX
            $paymentNumber = $this->generatePaymentNumber($companyId, $data['payment_date']);
            $user = Auth::user();

            // 5. Buat Record VendorPayment
            $payment = VendorPayment::create([
                'company_id' => $companyId,
                'lsp_id' => $data['lsp_id'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'trainer_id' => $data['trainer_id'] ?? null,
                'cash_bank_account_id' => $data['cash_bank_account_id'],
                'payment_number' => $paymentNumber,
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                'amount' => $amount,
                'reference_number' => $data['reference_number'] ?? null,
                'status' => 'posted',
                'notes' => $data['notes'] ?? null,
                'created_by' => $user?->id ?? 1,
            ]);

            // 6. Update Status Tiap Bill & Buat Allocations
            foreach ($allocations as $alloc) {
                $bill = VendorBill::findOrFail($alloc['vendor_bill_id']);
                $applyAmt = (float) $alloc['amount_applied'];

                VendorPaymentAllocation::create([
                    'vendor_payment_id' => $payment->id,
                    'vendor_bill_id' => $bill->id,
                    'amount_applied' => $applyAmt,
                ]);

                $newPaid = (float) $bill->paid_amount + $applyAmt;
                $newBalance = max(0.0, (float) $bill->balance_due - $applyAmt);
                $newStatus = ($newBalance <= 0.009) ? 'paid' : 'partial';

                $bill->update([
                    'paid_amount' => $newPaid,
                    'balance_due' => $newBalance,
                    'status' => $newStatus,
                ]);
            }

            // 7. Potong Saldo Kas/Bank & Buat Mutasi Bank Transaksi
            $cashBank->update([
                'current_balance' => (float) $cashBank->current_balance - $amount,
            ]);

            $payeeName = $payment->lsp?->name ?? $payment->vendor?->name ?? $payment->trainer?->name ?? 'Vendor';

            BankTransaction::create([
                'cash_bank_account_id' => $cashBank->id,
                'transaction_date' => $data['payment_date'],
                'transaction_type' => 'withdrawal',
                'amount' => $amount,
                'reference_number' => $paymentNumber,
                'source_type' => VendorPayment::class,
                'source_id' => $payment->id,
                'description' => "Pengeluaran Kas/Bank Voucher {$paymentNumber} - Bayar ke {$payeeName}",
                'is_reconciled' => true,
                'reconciled_at' => now(),
                'reconciled_by' => $user?->id,
            ]);

            // 8. Posting Jurnal Double-Entry Pelunasan Utang
            // Debit: 202 Utang Sertifikasi (jika LSP) atau 201 Utang Usaha (jika vendor)
            // Kredit: Akun Kas/Bank (101/102)
            $bankAccount = Account::findOrFail($cashBank->account_id);
            if ($isLspPayment || $data['lsp_id']) {
                $accLiability = Account::where('company_id', $companyId)->where('code', '202')->first();
            } else {
                $accLiability = Account::where('company_id', $companyId)->where('code', '201')->first();
            }

            if (! $accLiability) {
                $accLiability = Account::where('company_id', $companyId)->where('code', '201')->firstOrFail();
            }

            $journalLines = [
                [
                    'account_id' => $accLiability->id,
                    'debit' => $amount,
                    'credit' => 0.00,
                    'memo' => "Pelunasan Utang Voucher {$paymentNumber} ({$payeeName})",
                ],
                [
                    'account_id' => $bankAccount->id,
                    'debit' => 0.00,
                    'credit' => $amount,
                    'memo' => "Pengeluaran Kas/Bank {$cashBank->name} ({$paymentNumber})",
                ],
            ];

            $postingService = new JournalPostingService;
            $journal = $postingService->createAndPost([
                'company_id' => $companyId,
                'entry_date' => $data['payment_date'],
                'batch_id' => $firstBatchId,
                'source_module' => 'vendor_payment',
                'reference' => $payment,
                'description' => "Pembayaran Utang Voucher {$paymentNumber} - {$payeeName}",
                'lines' => $journalLines,
            ]);

            $payment->update(['journal_entry_id' => $journal->id]);

            // 9. Audit Trail
            AuditTrailService::log(
                'record_vendor_payment',
                $payment,
                null,
                ['payment_number' => $paymentNumber, 'amount' => $amount, 'journal_id' => $journal->id],
                "Pengeluaran Pembayaran Utang {$paymentNumber} sebesar Rp ".number_format($amount, 2)
            );

            return $payment;
        });
    }

    /**
     * Generate Nomor Voucher VPY-YYYY-MM-XXXX
     */
    protected function generatePaymentNumber(int $companyId, string $date): string
    {
        $yearMonth = date('Y-m', strtotime($date));
        $prefix = 'VPY-'.$yearMonth.'-';

        $lastNumber = VendorPayment::where('company_id', $companyId)
            ->where('payment_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('payment_number');

        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
