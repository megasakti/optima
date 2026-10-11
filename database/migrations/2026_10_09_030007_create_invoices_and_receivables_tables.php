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
        // 1. Invoices (Faktur Penjualan Pelatihan & Sertifikasi)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('training_programs')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('training_batches')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('invoice_number', 50)->index();
            $table->date('invoice_date')->index();
            $table->date('due_date')->index();
            $table->string('po_number', 100)->nullable()->comment('No Purchase Order / Kontrak / SPK');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_ppn_rate', 5, 2)->default(0.00)->comment('Tarif PPN (misal 11%)');
            $table->decimal('tax_ppn_amount', 15, 2)->default(0.00);
            $table->decimal('tax_pph23_rate', 5, 2)->default(0.00)->comment('Potongan PPh 23 jika ada (misal 2%)');
            $table->decimal('tax_pph23_amount', 15, 2)->default(0.00);
            $table->decimal('down_payment_applied', 15, 2)->default(0.00)->comment('Pengurangan DP dari unearned revenue');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);
            $table->enum('status', [
                'draft',
                'pending_approval',
                'issued',
                'partial',
                'paid',
                'overdue',
                'cancelled',
            ])->default('draft');
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'batch_id']);
            $table->index(['due_date', 'status']);
        });

        // 2. Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete()
                ->comment('Akun pendapatan (default 401 Pendapatan Jasa Pelatihan)');
            $table->enum('item_type', ['training', 'certification', 'assessment', 'consulting', 'other'])->default('training');
            $table->string('description', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index('invoice_id');
        });

        // 3. Customer Payments (Pembayaran & Kwitansi Pelanggan)
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('cash_bank_account_id')->constrained('cash_bank_accounts')->restrictOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('payment_number', 50)->index()->comment('Nomor Kwitansi/Kuitansi, misal REC-2026-03-0001');
            $table->date('payment_date')->index();
            $table->enum('payment_method', ['bank_transfer', 'cash', 'giro', 'credit_card'])->default('bank_transfer');
            $table->decimal('amount', 15, 2)->comment('Jumlah uang neto yang disetor ke kas/bank');
            $table->decimal('pph23_deducted', 15, 2)->default(0.00)->comment('Potongan PPh 23 dibuktikan bukti potong');
            $table->decimal('total_credited', 15, 2)->comment('Total pengurang piutang (amount + pph23_deducted)');
            $table->string('reference_number', 100)->nullable()->comment('No referensi bank / no bukti transfer');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'payment_number']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'status']);
        });

        // 4. Payment Allocations (Alokasi Pembayaran ke Satu atau Banyak Invoice / Parsial)
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_payment_id')->constrained('customer_payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('amount_applied', 15, 2)->comment('Nominal pelunasan untuk invoice ini');
            $table->timestamps();

            $table->index(['customer_payment_id', 'invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('customer_payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
