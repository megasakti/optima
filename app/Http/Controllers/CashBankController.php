<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\CashBankAccount;
use App\Services\TreasuryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CashBankController extends Controller
{
    /**
     * Dashboard Rekening Kas & Bank serta Mutasi Transaksi.
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $accounts = CashBankAccount::where('company_id', $companyId)
            ->with('account')
            ->orderBy('id')
            ->get();

        $selectedAccountId = $request->input('account_id', $accounts->first()?->id);

        $txQuery = BankTransaction::whereHas('cashBankAccount', fn ($q) => $q->where('company_id', $companyId))
            ->with(['cashBankAccount', 'journalEntry'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if ($selectedAccountId) {
            $txQuery->where('cash_bank_account_id', $selectedAccountId);
        }

        if ($request->filled('type')) {
            $txQuery->where('transaction_type', $request->type);
        }

        if ($request->filled('reconciled')) {
            $txQuery->where('is_reconciled', $request->reconciled === '1');
        }

        $transactions = $txQuery->paginate(25)->withQueryString();

        $totalLiquidity = $accounts->sum('current_balance');

        return view('cash_bank.index', compact('accounts', 'selectedAccountId', 'transactions', 'totalLiquidity'));
    }

    /**
     * Form Transfer Dana Antar Rekening Kas/Bank.
     */
    public function createTransfer(): View
    {
        $companyId = Auth::user()->company_id;

        $accounts = CashBankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cash_bank.transfer', compact('accounts'));
    }

    /**
     * Proses Pemindahbukuan / Transfer Kas/Bank.
     */
    public function storeTransfer(Request $request, TreasuryService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'from_account_id' => ['required', 'exists:cash_bank_accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'exists:cash_bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $service->transferFunds(
                $companyId,
                (int) $request->from_account_id,
                (int) $request->to_account_id,
                (float) $request->amount,
                $request->transfer_date,
                $request->reference_number,
                $request->description
            );

            return redirect()->route('cash-bank.index')
                ->with('success', 'Pemindahbukuan dana berhasil diproses dan jurnal mutasi kas telah diposting!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle status rekonsiliasi transaksi bank.
     */
    public function toggleReconcile(int $id, TreasuryService $service): RedirectResponse
    {
        $user = Auth::user();

        try {
            $tx = $service->toggleReconciliation($id, $user->id);
            $statusText = $tx->is_reconciled ? 'Terekonsiliasi' : 'Belum Rekonsiliasi';

            return back()->with('success', "Status rekonsiliasi transaksi diperbarui: {$statusText}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
