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
        // 1. Budgets (RAB - Rencana Anggaran Biaya & Target Margin Batch)
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('training_batches')->cascadeOnDelete();
            $table->string('budget_number', 50)->index();
            $table->decimal('target_participants', 8, 2)->default(0);
            $table->decimal('target_revenue_per_pax', 15, 2)->default(0.00);
            $table->decimal('total_revenue_budget', 15, 2)->default(0.00);
            $table->decimal('total_cost_budget', 15, 2)->default(0.00);
            $table->decimal('projected_gross_margin', 15, 2)->default(0.00);
            $table->decimal('projected_margin_percentage', 5, 2)->default(0.00);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'revised'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'batch_id']);
            $table->unique(['company_id', 'budget_number']);
            $table->index(['company_id', 'status']);
        });

        // 2. Budget Items (Pos Rincian Anggaran Beban Langsung)
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete()
                ->comment('Akun COA mapping (504 Biaya Sertifikasi, 505 Instruktur, 515 Hotel, dll)');
            $table->enum('category', [
                'lsp_certification',
                'trainer_fee',
                'hotel_venue',
                'catering',
                'training_kit_materials',
                'transport_travel',
                'marketing',
                'contingency_other'
            ])->default('training_kit_materials');
            $table->string('description', 255);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 50)->default('pax')->comment('pax, hari, paket, org');
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('total_budget', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index('budget_id');
        });

        // 3. Budget Approvals (Riwayat Persetujuan RAB)
        Schema::create('budget_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('action', ['submitted', 'approved', 'rejected', 'revision_requested']);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_approvals');
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('budgets');
    }
};
