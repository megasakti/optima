<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\CashBankAccount;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Lsp;
use App\Models\PaymentAllocation;
use App\Models\TrainingBatch;
use App\Models\User;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use Illuminate\Database\Seeder;

class FinancialDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $admin = User::first();
        $batch = TrainingBatch::first();
        $period = AccountingPeriod::where('company_id', $company->id)->where('is_closed', false)->first();
        $customer = Customer::first();
        $lsp = Lsp::first();
        $bankBca = CashBankAccount::where('company_id', $company->id)->where('account_type', 'bank')->first();

        // 1. Buat RAB (Budget) untuk Batch 01
        $budget = Budget::firstOrCreate(
            ['company_id' => $company->id, 'batch_id' => $batch->id],
            [
                'budget_number' => 'RAB-2026-03-001',
                'target_participants' => 12,
                'target_revenue_per_pax' => 6500000.00,
                'total_revenue_budget' => 78000000.00, // 12 x 6.5jt
                'total_cost_budget' => 45500000.00,
                'projected_gross_margin' => 32500000.00,
                'projected_margin_percentage' => 41.67,
                'status' => 'approved',
                'notes' => 'RAB Batch 01 disetujui Finance Manager',
                'created_by' => $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]
        );

        $accLsp = Account::where('company_id', $company->id)->where('code', '504')->first();
        $accTrainer = Account::where('company_id', $company->id)->where('code', '505')->first();
        $accHotel = Account::where('company_id', $company->id)->where('code', '515')->first();
        $accKit = Account::where('company_id', $company->id)->where('code', '503')->first();
        $accRevenue = Account::where('company_id', $company->id)->where('code', '401')->first();
        $accAR = Account::where('company_id', $company->id)->where('code', '103')->first();
        $accAPLsp = Account::where('company_id', $company->id)->where('code', '202')->first();
        $accBank = Account::where('company_id', $company->id)->where('code', '102')->first();
        $accTax23 = Account::where('company_id', $company->id)->where('code', '106')->first();

        // Budget Items
        BudgetItem::firstOrCreate(
            ['budget_id' => $budget->id, 'description' => 'Biaya Sertifikasi Uji Kompetensi LSP BNSP'],
            [
                'account_id' => $accLsp?->id,
                'category' => 'lsp_certification',
                'quantity' => 12,
                'unit' => 'asesi',
                'unit_price' => 1800000.00,
                'total_budget' => 21600000.00,
            ]
        );

        BudgetItem::firstOrCreate(
            ['budget_id' => $budget->id, 'description' => 'Honor Instruktur K3 (4 Hari)'],
            [
                'account_id' => $accTrainer?->id,
                'category' => 'trainer_fee',
                'quantity' => 4,
                'unit' => 'hari',
                'unit_price' => 3500000.00,
                'total_budget' => 14000000.00,
            ]
        );

        BudgetItem::firstOrCreate(
            ['budget_id' => $budget->id, 'description' => 'Paket Meeting Fullboard Hotel Santika'],
            [
                'account_id' => $accHotel?->id,
                'category' => 'hotel_venue',
                'quantity' => 1,
                'unit' => 'paket',
                'unit_price' => 7500000.00,
                'total_budget' => 7500000.00,
            ]
        );

        BudgetItem::firstOrCreate(
            ['budget_id' => $budget->id, 'description' => 'Bahan Uji, Hardcopy Modul & Perlengkapan K3'],
            [
                'account_id' => $accKit?->id,
                'category' => 'training_kit_materials',
                'quantity' => 12,
                'unit' => 'paket',
                'unit_price' => 200000.00,
                'total_budget' => 2400000.00,
            ]
        );

        // 2. Buat Transaksi Invoice AR: Tagihan 8 Peserta PT Semen Nusantara Perkasa
        // Total = 8 pax x 6.500.000 = Rp 52.000.000
        $invoice = Invoice::firstOrCreate(
            ['company_id' => $company->id, 'invoice_number' => 'INV-2026-03-0001'],
            [
                'customer_id' => $customer->id,
                'program_id' => $batch->program_id,
                'batch_id' => $batch->id,
                'invoice_date' => '2026-03-10',
                'due_date' => '2026-04-10',
                'po_number' => 'PO/SNP/K3/2026/089',
                'subtotal' => 52000000.00,
                'discount_amount' => 0.00,
                'total_amount' => 52000000.00,
                'paid_amount' => 26000000.00, // DP 50%
                'balance_due' => 26000000.00,
                'status' => 'partial',
                'notes' => 'Tagihan termin 1 (DP 50% telah diterima, sisa H+30 setelah asesmen)',
                'created_by' => $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]
        );

        InvoiceItem::firstOrCreate(
            ['invoice_id' => $invoice->id, 'description' => 'Pelatihan & Sertifikasi Ahli K3 Umum BNSP (8 Peserta)'],
            [
                'account_id' => $accRevenue?->id,
                'item_type' => 'training',
                'quantity' => 8,
                'unit_price' => 6500000.00,
                'discount' => 0.00,
                'total_price' => 52000000.00,
            ]
        );

        // Jurnal Otomatis Invoice:
        // Dr. Piutang Usaha (103) Rp 52.000.000
        //   Cr. Pendapatan Jasa Pelatihan (401) Rp 52.000.000
        if ($period && $accAR && $accRevenue) {
            $journalInv = JournalEntry::firstOrCreate(
                ['company_id' => $company->id, 'entry_number' => 'JV-INV-2026-03-0001'],
                [
                    'period_id' => $period->id,
                    'batch_id' => $batch->id,
                    'entry_date' => '2026-03-10',
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                    'source_module' => 'invoice',
                    'description' => 'Pengakuan Piutang Invoice '.$invoice->invoice_number.' - '.$customer->name,
                    'total_debit' => 52000000.00,
                    'total_credit' => 52000000.00,
                    'status' => 'posted',
                    'posted_at' => now(),
                    'posted_by' => $admin->id,
                ]
            );

            JournalLine::firstOrCreate([
                'journal_entry_id' => $journalInv->id,
                'account_id' => $accAR->id,
                'debit' => 52000000.00,
                'credit' => 0.00,
                'memo' => 'Piutang Invoice '.$invoice->invoice_number,
            ]);

            JournalLine::firstOrCreate([
                'journal_entry_id' => $journalInv->id,
                'account_id' => $accRevenue->id,
                'debit' => 0.00,
                'credit' => 52000000.00,
                'memo' => 'Pendapatan Jasa Pelatihan Batch 01 K3',
            ]);

            $invoice->update(['journal_entry_id' => $journalInv->id]);
        }

        // 3. Customer Payment (Penerimaan DP 50% = Rp 26.000.000 ke Bank BCA)
        if ($bankBca && $accBank && $accAR) {
            $payment = CustomerPayment::firstOrCreate(
                ['company_id' => $company->id, 'payment_number' => 'REC-2026-03-0001'],
                [
                    'customer_id' => $customer->id,
                    'cash_bank_account_id' => $bankBca->id,
                    'payment_date' => '2026-03-12',
                    'payment_method' => 'bank_transfer',
                    'amount' => 26000000.00,
                    'pph23_deducted' => 0.00,
                    'total_credited' => 26000000.00,
                    'reference_number' => 'TRF-BCA-9921102',
                    'status' => 'posted',
                    'notes' => 'Pembayaran DP 50% Pelatihan K3 Umum Batch 01',
                    'created_by' => $admin->id,
                ]
            );

            PaymentAllocation::firstOrCreate(
                ['customer_payment_id' => $payment->id, 'invoice_id' => $invoice->id],
                ['amount_applied' => 26000000.00]
            );

            // Jurnal Penerimaan Pembayaran:
            // Dr. Kas Bank BCA (102) Rp 26.000.000
            //   Cr. Piutang Usaha (103) Rp 26.000.000
            $journalPay = JournalEntry::firstOrCreate(
                ['company_id' => $company->id, 'entry_number' => 'JV-REC-2026-03-0001'],
                [
                    'period_id' => $period->id,
                    'batch_id' => $batch->id,
                    'entry_date' => '2026-03-12',
                    'reference_type' => CustomerPayment::class,
                    'reference_id' => $payment->id,
                    'source_module' => 'customer_payment',
                    'description' => 'Penerimaan Kas/Bank Pembayaran '.$payment->payment_number,
                    'total_debit' => 26000000.00,
                    'total_credit' => 26000000.00,
                    'status' => 'posted',
                    'posted_at' => now(),
                    'posted_by' => $admin->id,
                ]
            );

            JournalLine::firstOrCreate([
                'journal_entry_id' => $journalPay->id,
                'account_id' => $accBank->id,
                'debit' => 26000000.00,
                'credit' => 0.00,
                'memo' => 'Penerimaan Transfer Bank BCA '.$customer->name,
            ]);

            JournalLine::firstOrCreate([
                'journal_entry_id' => $journalPay->id,
                'account_id' => $accAR->id,
                'debit' => 0.00,
                'credit' => 26000000.00,
                'memo' => 'Pelunasan Sebagian Piutang Invoice '.$invoice->invoice_number,
            ]);

            $payment->update(['journal_entry_id' => $journalPay->id]);
        }

        // 4. Tagihan Utang Sertifikasi LSP (Vendor Bill)
        // Tagihan 12 asesi x Rp 1.800.000 = Rp 21.600.000
        if ($lsp && $accLsp && $accAPLsp) {
            $bill = VendorBill::firstOrCreate(
                ['company_id' => $company->id, 'bill_number' => 'BILL-2026-03-0001'],
                [
                    'lsp_id' => $lsp->id,
                    'batch_id' => $batch->id,
                    'vendor_invoice_number' => 'INV/LSP-K3/2026/041',
                    'bill_date' => '2026-03-15',
                    'due_date' => '2026-03-29',
                    'subtotal' => 21600000.00,
                    'total_amount' => 21600000.00,
                    'paid_amount' => 0.00,
                    'balance_due' => 21600000.00,
                    'status' => 'approved',
                    'notes' => 'Tagihan asesmen uji kompetensi K3 12 asesi',
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]
            );

            VendorBillItem::firstOrCreate(
                ['vendor_bill_id' => $bill->id, 'description' => 'Uji Kompetensi & Sertifikat BNSP Ahli K3 Umum (12 asesi)'],
                [
                    'account_id' => $accLsp->id,
                    'quantity' => 12,
                    'unit_price' => 1800000.00,
                    'total_price' => 21600000.00,
                ]
            );

            // Jurnal Tagihan LSP:
            // Dr. Biaya Sertifikasi (504) Rp 21.600.000
            //   Cr. Utang Sertifikasi (202) Rp 21.600.000
            $journalBill = JournalEntry::firstOrCreate(
                ['company_id' => $company->id, 'entry_number' => 'JV-BILL-2026-03-0001'],
                [
                    'period_id' => $period->id,
                    'batch_id' => $batch->id,
                    'entry_date' => '2026-03-15',
                    'reference_type' => VendorBill::class,
                    'reference_id' => $bill->id,
                    'source_module' => 'vendor_bill',
                    'description' => 'Tagihan Asesmen Sertifikasi LSP '.$bill->bill_number,
                    'total_debit' => 21600000.00,
                    'total_credit' => 21600000.00,
                    'status' => 'posted',
                    'posted_at' => now(),
                    'posted_by' => $admin->id,
                ]
            );

            JournalLine::firstOrCreate([
                'journal_entry_id' => $journalBill->id,
                'account_id' => $accLsp->id,
                'debit' => 21600000.00,
                'credit' => 0.00,
                'memo' => 'Biaya Sertifikasi LSP Batch 01 K3',
            ]);

            JournalLine::firstOrCreate([
                'journal_entry_id' => $journalBill->id,
                'account_id' => $accAPLsp->id,
                'debit' => 0.00,
                'credit' => 21600000.00,
                'memo' => 'Utang Sertifikasi ke '.$lsp->name,
            ]);

            $bill->update(['journal_entry_id' => $journalBill->id]);
        }
    }
}
