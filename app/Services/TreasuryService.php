<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\CashBankAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TreasuryService
{
    /**
     * Transfer dana antar rekening kas/bank (misal: Bank BCA ke Kas Kecil Operasional).
     */
    public function transferFunds(
        int $companyId,
        int $fromAccountId,
        int $toAccountId,
        float $amount,
        string $date,
        ?string $referenceNumber = null,
        ?string $description = null
    ): array {
        return DB::transaction(function () use ($companyId, $fromAccountId, $toAccountId, $amount, $date, $referenceNumber, $description) {
            if ($fromAccountId === $toAccountId) {
                throw new InvalidArgumentException("Rekening asal dan rekening tujuan transfer tidak boleh sama.");
            }

            if ($amount <= 0) {
                throw new InvalidArgumentException("Nominal transfer harus lebih besar dari 0.");
            }

            $fromAcc = CashBankAccount::where('company_id', $companyId)->lockForUpdate()->findOrFail($fromAccountId);
            $toAcc = CashBankAccount::where('company_id', $companyId)->lockForUpdate()->findOrFail($toAccountId);

            if ((float) $fromAcc->current_balance < $amount) {
                throw new InvalidArgumentException(
                    "Saldo {$fromAcc->name} (Rp " . number_format($fromAcc->current_balance, 2) . ") tidak mencukupi untuk transfer Rp " . number_format($amount, 2) . "."
                );
            }

            // 1. Update Saldo Kas/Bank
            $fromAcc->update(['current_balance' => (float) $fromAcc->current_balance - $amount]);
            $toAcc->update(['current_balance' => (float) $toAcc->current_balance + $amount]);

            $user = Auth::user();
            $memo = $description ?: "Transfer dana dari {$fromAcc->name} ke {$toAcc->name}";

            // 2. Buat Mutasi Bank
            $txOut = BankTransaction::create([
                'cash_bank_account_id' => $fromAcc->id,
                'transaction_date' => $date,
                'transaction_type' => 'transfer_out',
                'amount' => $amount,
                'reference_number' => $referenceNumber,
                'description' => "Transfer Keluar ke {$toAcc->name} - " . $memo,
                'is_reconciled' => true,
                'reconciled_at' => now(),
                'reconciled_by' => $user?->id,
            ]);

            $txIn = BankTransaction::create([
                'cash_bank_account_id' => $toAcc->id,
                'transaction_date' => $date,
                'transaction_type' => 'transfer_in',
                'amount' => $amount,
                'reference_number' => $referenceNumber,
                'description' => "Transfer Masuk dari {$fromAcc->name} - " . $memo,
                'is_reconciled' => true,
                'reconciled_at' => now(),
                'reconciled_by' => $user?->id,
            ]);

            // 3. Posting Jurnal Double-Entry Antar Kas/Bank
            // Debit: Rekening Kas/Bank Tujuan
            // Kredit: Rekening Kas/Bank Asal
            $postingService = new JournalPostingService();
            $journal = $postingService->createAndPost([
                'company_id' => $companyId,
                'entry_date' => $date,
                'batch_id' => null,
                'source_module' => 'bank_transaction',
                'description' => $memo,
                'lines' => [
                    [
                        'account_id' => $toAcc->account_id,
                        'debit' => $amount,
                        'credit' => 0.00,
                        'memo' => "Penerimaan Transfer ke {$toAcc->name}",
                    ],
                    [
                        'account_id' => $fromAcc->account_id,
                        'debit' => 0.00,
                        'credit' => $amount,
                        'memo' => "Pengeluaran Transfer dari {$fromAcc->name}",
                    ],
                ],
            ]);

            $txOut->update(['journal_entry_id' => $journal->id]);
            $txIn->update(['journal_entry_id' => $journal->id]);

            // 4. Audit Trail
            AuditTrailService::log(
                'treasury_transfer',
                $fromAcc,
                ['from' => $fromAcc->name, 'to' => $toAcc->name],
                ['amount' => $amount, 'journal_id' => $journal->id],
                "Transfer kas/bank senilai Rp " . number_format($amount, 2) . " dari {$fromAcc->name} ke {$toAcc->name}"
            );

            return [
                'from_transaction' => $txOut,
                'to_transaction' => $txIn,
                'journal' => $journal,
            ];
        });
    }

    /**
     * Toggle rekonsiliasi transaksi bank.
     */
    public function toggleReconciliation(int $transactionId, int $userId): BankTransaction
    {
        $tx = BankTransaction::findOrFail($transactionId);

        $newStatus = !$tx->is_reconciled;
        $tx->update([
            'is_reconciled' => $newStatus,
            'reconciled_at' => $newStatus ? now() : null,
            'reconciled_by' => $newStatus ? $userId : null,
        ]);

        return $tx;
    }
}
