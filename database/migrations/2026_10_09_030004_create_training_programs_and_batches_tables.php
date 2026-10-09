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
        // 1. Training Programs (Skema / Silabus / Paket)
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('lsp_id')->nullable()->constrained('lsps')->nullOnDelete();
            $table->string('code', 30)->index();
            $table->string('name', 150);
            $table->string('skkni_scheme_name', 200)->nullable()->comment('Nama Skema BNSP');
            $table->enum('category', ['certification', 'non_certification', 'consulting', 'inhouse'])->default('certification');
            $table->integer('duration_days')->default(2);
            $table->decimal('default_price_per_participant', 15, 2)->default(0.00);
            $table->decimal('default_lsp_cost_per_participant', 15, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // 2. Training Batches (Angkatan Pelatihan - Unit Costing & Profitability)
        Schema::create('training_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('training_programs')->restrictOnDelete();
            $table->foreignId('lsp_id')->nullable()->constrained('lsps')->nullOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('batch_code', 50)->index()->comment('Kode unik angkatan, misal BTH-2026-03-001');
            $table->string('name', 200);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('assessment_date')->nullable()->comment('Tanggal Asesmen BNSP');
            $table->enum('delivery_type', ['offline_hotel', 'offline_inhouse', 'online', 'hybrid'])->default('offline_hotel');
            $table->string('venue_name', 150)->nullable();
            $table->string('venue_city', 100)->nullable();
            $table->integer('target_participants')->default(10);
            $table->integer('actual_participants')->default(0);
            $table->decimal('price_per_participant', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'confirmed', 'ongoing', 'completed', 'evaluated', 'closed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'batch_code']);
            $table->index(['company_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_batches');
        Schema::dropIfExists('training_programs');
    }
};
