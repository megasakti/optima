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
        // 1. Cash & Bank Accounts (Buku Pembantu Kas / Rekening Bank)
        Schema::create('cash_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete()
                ->comment('Tautan ke akun neraca (101 Kas, 102 Bank BCA, dst)');
            $table->string('name', 100)->comment('Nama identifikasi, misal: Kas Operasional, BCA Giro');
            $table->enum('account_type', ['cash', 'bank', 'petty_cash'])->default('bank');
            $table->string('bank_name', 50)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_holder', 100)->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->decimal('initial_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'account_id']);
        });

        // 2. Bank Transactions (Mutasi Transaksi Buku Bank & Rekonsiliasi)
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_bank_account_id')->constrained('cash_bank_accounts')->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->date('transaction_date')->index();
            $table->enum('transaction_type', ['deposit', 'withdrawal', 'transfer_in', 'transfer_out']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_number', 100)->nullable();
            $table->nullableMorphs('source'); // source_type & source_id (Payment, VendorPayment, dll)
            $table->text('description');
            $table->boolean('is_reconciled')->default(false);
            $table->dateTime('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['cash_bank_account_id', 'transaction_date']);
            $table->index(['cash_bank_account_id', 'is_reconciled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('cash_bank_accounts');
    }
};
