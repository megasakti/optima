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
        // 1. Vendor Bills (Tagihan LSP, Vendor Hotel, Transport, ATK)
        Schema::create('vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('lsp_id')->nullable()->constrained('lsps')->nullOnDelete()
                ->comment('Jika tagihan berasal dari Lembaga Sertifikasi Profesi');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete()
                ->comment('Jika tagihan berasal dari Vendor umum');
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete()
                ->comment('Jika tagihan berupa honor narasumber');
            $table->foreignId('batch_id')->nullable()->constrained('training_batches')->nullOnDelete()
                ->comment('Kaitan biaya langsung batch');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('bill_number', 50)->index()->comment('No internal tagihan sistem, misal BILL-2026-03-0001');
            $table->string('vendor_invoice_number', 100)->nullable()->comment('No faktur/kuitansi asli dari vendor/LSP');
            $table->date('bill_date')->index();
            $table->date('due_date')->index();
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'partial',
                'paid',
                'cancelled'
            ])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'bill_number']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'lsp_id']);
            $table->index(['company_id', 'vendor_id']);
            $table->index(['company_id', 'batch_id']);
            $table->index(['due_date', 'status']);
        });

        // 2. Vendor Bill Items (Pos Beban Akun Terkait)
        Schema::create('vendor_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete()
                ->comment('Akun beban, misal 504 Biaya Sertifikasi atau 515 Biaya Hotel');
            $table->string('description', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['vendor_bill_id', 'account_id']);
        });

        // 3. Vendor Payments (Pembayaran Utang LSP & Vendor)
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('lsp_id')->nullable()->constrained('lsps')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->foreignId('cash_bank_account_id')->constrained('cash_bank_accounts')->restrictOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('payment_number', 50)->index()->comment('No voucher pengeluaran, misal VPY-2026-03-0001');
            $table->date('payment_date')->index();
            $table->enum('payment_method', ['bank_transfer', 'cash', 'giro'])->default('bank_transfer');
            $table->decimal('amount', 15, 2)->comment('Total dana yang dibayarkan');
            $table->string('reference_number', 100)->nullable()->comment('No referensi transfer bank');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'payment_number']);
            $table->index(['company_id', 'status']);
        });

        // 4. Vendor Payment Allocations
        Schema::create('vendor_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_payment_id')->constrained('vendor_payments')->cascadeOnDelete();
            $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->cascadeOnDelete();
            $table->decimal('amount_applied', 15, 2);
            $table->timestamps();

            $table->index(['vendor_payment_id', 'vendor_bill_id'], 'vpay_alloc_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payment_allocations');
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('vendor_bill_items');
        Schema::dropIfExists('vendor_bills');
    }
};
