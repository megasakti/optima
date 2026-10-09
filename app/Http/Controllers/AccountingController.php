<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\TrainingBatch;
use App\Services\GeneralLedgerService;
use App\Services\JournalPostingService;
use App\Services\JournalReversalService;
use App\Services\JournalImportService;
use App\Services\PeriodClosingService;
use App\Services\TrialBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountingController extends Controller
{
    /**
     * 1. Daftar Chart of Accounts (COA)
     */
    public function accounts(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = Account::where('company_id', $companyId)
            ->with(['parent', 'cashBankAccount'])
            ->orderBy('code');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $accounts = $query->paginate(25)->withQueryString();
        $parentAccounts = Account::where('company_id', $companyId)->orderBy('code')->get();

        return view('accounting.accounts.index', compact('accounts', 'parentAccounts'));
    }

    /**
     * Simpan Akun Baru di COA
     */
    public function storeAccount(Request $request): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:accounts,code,NULL,id,company_id,' . $companyId],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'normal_balance' => ['required', 'in:debit,credit'],
            'category' => ['required', 'string'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_cash_bank' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['company_id'] = $companyId;
        $validated['is_cash_bank'] = $request->boolean('is_cash_bank');
        $validated['is_active'] = true;

        Account::create($validated);

        return back()->with('success', "Akun {$validated['code']} - {$validated['name']} berhasil ditambahkan ke COA.");
    }

    /**
     * 2. Daftar Jurnal Umum (Journal Entries)
     */
    public function journals(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = JournalEntry::where('company_id', $companyId)
            ->with(['period', 'batch', 'lines.account', 'postedBy'])
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        if ($request->filled('source_module')) {
            $query->where('source_module', $request->source_module);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $journals = $query->paginate(20)->withQueryString();
        $periods = AccountingPeriod::where('company_id', $companyId)->orderBy('start_date', 'desc')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->orderBy('batch_code')->get();

        return view('accounting.journals.index', compact('journals', 'periods', 'batches'));
    }

    /**
     * Unduh Template Excel (.xlsx) untuk Bulk Import Jurnal Umum
     */
    public function downloadJournalTemplate(JournalImportService $importService): StreamedResponse
    {
        $companyId = Auth::user()->company_id;
        $spreadsheet = $importService->generateTemplate($companyId);

        $fileName = 'Template_Bulk_Import_Jurnal_Optima.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Eksekusi Bulk Import Jurnal Umum dari File Excel
     */
    public function importJournals(Request $request, JournalImportService $importService): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        try {
            $result = $importService->import(
                $request->file('file'),
                Auth::user()->company_id,
                Auth::user()
            );

            $fmtAmount = number_format($result['total_amount'], 0, ',', '.');
            return redirect()->route('accounting.journals.index')
                ->with('success', "Berhasil mengimpor {$result['journals_count']} transaksi jurnal umum ({$result['lines_count']} baris) dengan total mutasi Rp {$fmtAmount}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Form Input Jurnal Manual
     */
    public function createJournal(): View
    {
        $companyId = Auth::user()->company_id;
        $accounts = Account::where('company_id', $companyId)->where('is_active', true)->orderBy('code')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->orderBy('batch_code')->get();
        $activePeriods = AccountingPeriod::where('company_id', $companyId)->where('is_closed', false)->get();

        return view('accounting.journals.create', compact('accounts', 'batches', 'activePeriods'));
    }

    /**
     * Simpan dan Posting Jurnal Manual
     */
    public function storeJournal(Request $request, JournalPostingService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'batch_id' => ['nullable', 'exists:training_batches,id'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.memo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $journal = $service->createAndPost([
                'company_id' => $companyId,
                'entry_date' => $request->entry_date,
                'description' => $request->description,
                'batch_id' => $request->batch_id,
                'source_module' => 'general',
                'lines' => $request->lines,
            ]);

            return redirect()->route('accounting.journals.show', $journal->id)
                ->with('success', "Jurnal {$journal->entry_number} berhasil diposting!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan Detail Jurnal
     */
    public function showJournal(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $journal = JournalEntry::where('company_id', $companyId)
            ->with(['period', 'batch', 'lines.account', 'postedBy', 'reversedBy', 'reference'])
            ->findOrFail($id);

        return view('accounting.journals.show', compact('journal'));
    }

    /**
     * Pembalikan Jurnal (Reversal / Storno)
     */
    public function reverseJournal(Request $request, int $id, JournalReversalService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $journal = JournalEntry::where('company_id', $companyId)->findOrFail($id);

        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'reversal_date' => ['nullable', 'date'],
        ]);

        try {
            $reversal = $service->reverse($journal, $request->reason, $request->reversal_date);

            return redirect()->route('accounting.journals.show', $reversal->id)
                ->with('success', "Jurnal {$journal->entry_number} berhasil dibalik dengan Jurnal Pembalik {$reversal->entry_number}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 3. Buku Besar (General Ledger)
     */
    public function ledger(Request $request, GeneralLedgerService $service): View
    {
        $companyId = Auth::user()->company_id;

        $accounts = Account::where('company_id', $companyId)->where('is_active', true)->orderBy('code')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->orderBy('batch_code')->get();

        $selectedAccountId = $request->input('account_id', $accounts->first()?->id);
        $startDate = $request->input('start_date', date('Y-01-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $batchId = $request->filled('batch_id') ? (int) $request->batch_id : null;

        $ledgerData = null;
        if ($selectedAccountId) {
            $account = Account::where('company_id', $companyId)->findOrFail($selectedAccountId);
            $ledgerData = $service->getAccountLedger($account, $startDate, $endDate, $batchId);
        }

        return view('accounting.ledger.index', compact('accounts', 'batches', 'selectedAccountId', 'startDate', 'endDate', 'batchId', 'ledgerData'));
    }

    /**
     * 4. Neraca Saldo (Trial Balance)
     */
    public function trialBalance(Request $request, TrialBalanceService $service): View
    {
        $companyId = Auth::user()->company_id;
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));

        $trialBalance = $service->generate($companyId, $asOfDate);

        return view('accounting.trial_balance.index', compact('trialBalance', 'asOfDate'));
    }

    /**
     * 5. Periode Akuntansi & Tutup Buku
     */
    public function periods(): View
    {
        $companyId = Auth::user()->company_id;
        $periods = AccountingPeriod::where('company_id', $companyId)
            ->with('closedBy')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('accounting.periods.index', compact('periods'));
    }

    /**
     * Simpan Periode Baru
     */
    public function storePeriod(Request $request): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['company_id'] = $companyId;
        $validated['is_closed'] = false;

        AccountingPeriod::create($validated);

        return back()->with('success', "Periode akuntansi {$validated['name']} berhasil dibuat.");
    }

    /**
     * Eksekusi Tutup Buku
     */
    public function closePeriod(Request $request, int $id, PeriodClosingService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $period = AccountingPeriod::where('company_id', $companyId)->findOrFail($id);

        $request->validate([
            'closing_date' => ['required', 'date'],
        ]);

        try {
            $closingJournal = $service->closePeriod($period, $request->closing_date);

            return back()->with('success', "Tutup buku {$period->name} berhasil! Jurnal Penutup: {$closingJournal->entry_number}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
