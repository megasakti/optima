<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDataExceptCoaAndUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-operational-data {--force : Jalankan tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengosongkan seluruh data transaksi dan operasional, mengecualikan Bagan Akun (COA), Manajemen Pengguna/PIC, Company, dan RBAC.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('Perhatian: Perintah ini akan mengosongkan data pada tabel transaksi dan master operasional!');
        $this->info('Tabel yang DIKECUALIKAN dari penghapusan:');
        $this->line(' - Bagan Akun (accounts)');
        $this->line(' - Pengguna / PIC (users)');
        $this->line(' - Role & Permissions (roles, permissions, model_has_roles, dll)');
        $this->line(' - Data Perusahaan (companies)');
        $this->line(' - Migrasi Sistem (migrations)');

        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin melanjutkan pengosongan data?')) {
            $this->info('Operasi dibatalkan.');

            return Command::SUCCESS;
        }

        // Daftar tabel yang dikosongkan (diurutkan untuk keamanan foreign key cascade/set null)
        $tablesToTruncate = [
            // Transaksi Pembayaran & Alokasi
            'payment_allocations',
            'customer_payments',
            'vendor_payments',

            // Invoicing AR & AP Bills
            'invoice_items',
            'invoices',
            'vendor_bill_items',
            'vendor_bills',

            // Transaksi Kas Bank
            'cash_bank_transactions',

            // Jurnal Umum & Buku Besar
            'journal_lines',
            'journal_entries',

            // Budgeting & RAB
            'budget_items',
            'budgets',

            // Operasional Batch Pelatihan & Skema
            'training_batches',
            'training_programs',

            // Kontak Master Eksternal
            'customers',
            'lsps',
            'vendors',
            'trainers',

            // Log & Permohonan
            'deletion_requests',
            'audit_logs',
            'accounting_periods',
        ];

        Schema::disableForeignKeyConstraints();

        $clearedCount = 0;
        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->line(" <fg=green>✓</> Mengosongkan tabel <options=bold>{$table}</> ({$count} baris dihapus)");
                $clearedCount++;
            }
        }

        // Reset saldo rekening kas bank jika tabel ada
        if (Schema::hasTable('cash_bank_accounts')) {
            DB::table('cash_bank_accounts')->update([
                'initial_balance' => 0.00,
                'current_balance' => 0.00,
            ]);
            $this->line(' <fg=green>✓</> Mereset saldo tabel <options=bold>cash_bank_accounts</> menjadi Rp 0.00');
        }

        Schema::enableForeignKeyConstraints();

        $this->newLine();
        $this->info("Berhasil! {$clearedCount} tabel operasional dan transaksi telah dikosongkan.");
        $this->info('Bagan Akun (Chart of Accounts) dan data Pengguna/PIC tetap utuh dan siap digunakan.');

        return Command::SUCCESS;
    }
}
