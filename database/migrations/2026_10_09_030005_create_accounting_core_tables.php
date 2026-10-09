<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Chart of Accounts (COA)
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('code', 30)->index()->comment('Kode Akun, misal 101, 102, 504');
            $table->string('name', 150);
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->enum('category', [
                'current_asset',
                'non_current_asset',
                'current_liability',
                'long_term_liability',
                'equity',
                'operating_revenue',
                'direct_cost',          // HPP Batch (Sertifikasi, Instruktur, Hotel, dll)
                'operating_expense',      // Beban Operasional Umum (Gaji, Listrik, Sewa)
                'other_expense_income'
            ])->default('operating_expense');
            $table->boolean('is_cash_bank')->default(false);
            $table->boolean('is_reconcilable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'category']);
        });

        // 2. Accounting Periods (Periode Akuntansi & Tutup Buku)
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 100)->comment('Nama periode, misal "Maret 2026" atau "Tahun 2026"');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false);
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'start_date', 'end_date']);
            $table->index(['company_id', 'is_closed']);
        });

        // 3. Journal Entries (Kepala Jurnal Akuntansi)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('accounting_periods')->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('training_batches')->nullOnDelete()
                ->comment('Kaitan ke batch pelatihan untuk analitik profitabilitas');
            $table->string('entry_number', 50)->index()->comment('Nomor unik jurnal, misal JV-2026-03-0001');
            $table->date('entry_date')->index();
            $table->nullableMorphs('reference'); // reference_type & reference_id (Invoice, Payment, Bill, etc.)
            $table->enum('source_module', [
                'general',
                'invoice',
                'customer_payment',
                'vendor_bill',
                'vendor_payment',
                'bank_transaction',
                'reversal',
                'closing'
            ])->default('general');
            $table->text('description');
            $table->decimal('total_debit', 15, 2)->default(0.00);
            $table->decimal('total_credit', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->dateTime('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reversed_by_id')->nullable()->constrained('journal_entries')->nullOnDelete()
                ->comment('Referensi jurnal pembalik jika status reversed');
            $table->timestamps();

            $table->unique(['company_id', 'entry_number']);
            $table->index(['company_id', 'entry_date']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'batch_id']);
        });

        // 4. Journal Lines (Rincian Baris Debit / Kredit)
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('debit', 15, 2)->default(0.00);
            $table->decimal('credit', 15, 2)->default(0.00);
            $table->string('memo', 255)->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id', 'account_id']);
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('accounts');
    }
};
