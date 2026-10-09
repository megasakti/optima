<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\CashBankAccount;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CustomerPaymentService
{
    /**
     * Catat penerimaan pembayaran pelanggan dan alokasikan ke invoice.
     *
     * @param array{
     *     company_id: int,
     *     customer_id: int,
     *     cash_bank_account_id: int,
     *     payment_date: string,
     *     payment_method: string,
     *     amount: float,
     *     pph23_deducted?: float|null,
     *     reference_number?: string|null,
     *     notes?: string|null,
     *     allocations: array<array{invoice_id: int, amount_applied: float}>
     * } $data
     */
    public function recordPayment(array $data): CustomerPayment
    {
        return DB::transaction(function () use ($data) {
            $companyId = $data['company_id'];
            $amount = (float) $data['amount'];
            $pph23 = (float) ($data['pph23_deducted'] ?? 0);
            $totalCredited = $amount + $pph23;

            $allocations = $data['allocations'] ?? [];
            if (empty($allocations)) {
                throw new InvalidArgumentException("Pembayaran wajib dialokasikan ke minimal 1 invoice tagihan.");
            }

            // 1. Validasi Total Alokasi
            $sumAllocated = 0.0;
            foreach ($allocations as $alloc) {
                $sumAllocated += (float) $alloc['amount_applied'];
            }

            if (bccomp((string) $sumAllocated, (string) $totalCredited, 2) !== 0) {
                throw new InvalidArgumentException(
                    "Total alokasi pelunasan (Rp " . number_format($sumAllocated, 2) . ") harus sama dengan total kredit pembayaran (Rp " . number_format($totalCredited, 2) . ")."
                );
            }

            // 2. Validasi Invoice dan Sisa Tagihan (Pessimistic Lock)
            foreach ($allocations as $alloc) {
                $inv = Invoice::where('company_id', $companyId)->lockForUpdate()->findOrFail($alloc['invoice_id']);
                $applyAmt = (float) $alloc['amount_applied'];

                if ($applyAmt > (float) $inv->balance_due + 0.01) {
                    throw new InvalidArgumentException(
                        "Alokasi pembayaran Rp " . number_format($applyAmt, 2) . " melebihi sisa tagihan Invoice {$inv->invoice_number} (Rp " . number_format($inv->balance_due, 2) . ")."
                    );
                }
            }

            // 3. Generate Nomor Kwitansi Pembayaran
            $paymentNumber = $this->generatePaymentNumber($companyId, $data['payment_date']);
            $user = Auth::user();

            // 4. Buat Record CustomerPayment
            $payment = CustomerPayment::create([
                'company_id' => $companyId,
                'customer_id' => $data['customer_id'],
                'cash_bank_account_id' => $data['cash_bank_account_id'],
                'payment_number' => $paymentNumber,
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                'amount' => $amount,
                'pph23_deducted' => $pph23,
                'total_credited' => $totalCredited,
                'reference_number' => $data['reference_number'] ?? null,
                'status' => 'posted',
                'notes' => $data['notes'] ?? null,
                'created_by' => $user?->id ?? 1,
            ]);

            // 5. Update Status Setiap Invoice & Buat PaymentAllocations
            $firstBatchId = null;
            foreach ($allocations as $alloc) {
                $inv = Invoice::findOrFail($alloc['invoice_id']);
                $applyAmt = (float) $alloc['amount_applied'];

                PaymentAllocation::create([
                    'customer_payment_id' => $payment->id,
                    'invoice_id' => $inv->id,
                    'amount_applied' => $applyAmt,
                ]);

                $newPaid = (float) $inv->paid_amount + $applyAmt;
                $newBalance = max(0.0, (float) $inv->balance_due - $applyAmt);
                $newStatus = ($newBalance <= 0.009) ? 'paid' : 'partial';

                $inv->update([
                    'paid_amount' => $newPaid,
                    'balance_due' => $newBalance,
                    'status' => $newStatus,
                ]);

                if (!$firstBatchId && $inv->batch_id) {
                    $firstBatchId = $inv->batch_id;
                }
            }

            // 6. Update Saldo Kas/Bank & Buat Mutasi Bank
            $cashBank = CashBankAccount::where('company_id', $companyId)->lockForUpdate()->findOrFail($data['cash_bank_account_id']);
            $cashBank->update([
                'current_balance' => (float) $cashBank->current_balance + $amount,
            ]);

            BankTransaction::create([
                'cash_bank_account_id' => $cashBank->id,
                'transaction_date' => $data['payment_date'],
                'transaction_type' => 'deposit',
                'amount' => $amount,
                'reference_number' => $paymentNumber,
                'source_type' => CustomerPayment::class,
                'source_id' => $payment->id,
                'description' => "Penerimaan Pembayaran Kwitansi {$paymentNumber} - {$payment->customer->name}",
                'is_reconciled' => true,
                'reconciled_at' => now(),
                'reconciled_by' => $user?->id,
            ]);

            // 7. Posting Jurnal Double-Entry Kas & Piutang
            // Akun Debit: Kas/Bank (101/102) & Pajak Dibayar di Muka PPh 23 (106)
            // Akun Kredit: Piutang Usaha (103)
            $bankAccount = Account::findOrFail($cashBank->account_id);
            $accAR = Account::where('company_id', $companyId)->where('code', '103')->first();
            $accTax23 = Account::where('company_id', $companyId)->where('code', '106')->first();

            $journalLines = [];

            // Debit Kas/Bank sebesar uang masuk
            $journalLines[] = [
                'account_id' => $bankAccount->id,
                'debit' => $amount,
                'credit' => 0.00,
                'memo' => "Penerimaan Kas/Bank {$cashBank->name} ({$paymentNumber})",
            ];

            // Debit Pajak Dibayar di Muka PPh 23 jika ada
            if ($pph23 > 0 && $accTax23) {
                $journalLines[] = [
                    'account_id' => $accTax23->id,
                    'debit' => $pph23,
                    'credit' => 0.00,
                    'memo' => "Bukti Potong PPh 23 Pelanggan ({$paymentNumber})",
                ];
            }

            // Kredit Piutang Usaha
            $journalLines[] = [
                'account_id' => $accAR->id,
                'debit' => 0.00,
                'credit' => $totalCredited,
                'memo' => "Pelunasan Piutang Kwitansi {$paymentNumber}",
            ];

            $postingService = new JournalPostingService();
            $journal = $postingService->createAndPost([
                'company_id' => $companyId,
                'entry_date' => $data['payment_date'],
                'batch_id' => $firstBatchId,
                'source_module' => 'customer_payment',
                'reference' => $payment,
                'description' => "Penerimaan Pembayaran Kwitansi {$paymentNumber} - {$payment->customer->name}",
                'lines' => $journalLines,
            ]);

            $payment->update(['journal_entry_id' => $journal->id]);

            // 8. Audit Log
            AuditTrailService::log(
                'record_customer_payment',
                $payment,
                null,
                ['payment_number' => $paymentNumber, 'amount' => $amount, 'journal_id' => $journal->id],
                "Penerimaan Pembayaran Pelanggan {$paymentNumber} sebesar Rp " . number_format($amount, 2)
            );

            return $payment;
        });
    }

    /**
     * Generate Nomor Kwitansi REC-YYYY-MM-XXXX
     */
    protected function generatePaymentNumber(int $companyId, string $date): string
    {
        $yearMonth = date('Y-m', strtotime($date));
        $prefix = "REC-" . $yearMonth . "-";

        $lastNumber = CustomerPayment::where('company_id', $companyId)
            ->where('payment_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('payment_number');

        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
