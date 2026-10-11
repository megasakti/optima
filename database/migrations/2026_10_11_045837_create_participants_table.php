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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete()->comment('Relasi ke Master Klien/Perusahaan');
            $table->string('company_name', 150)->nullable()->comment('Nama Perusahaan / Klien');
            $table->enum('category', ['public', 'iht', 'webinar'])->default('public')->comment('Kategori: Public, IHT, Webinar');
            $table->string('name', 150)->comment('Nama Peserta');
            $table->string('phone', 50)->nullable()->comment('No Telp / WhatsApp');
            $table->string('email', 100)->nullable()->comment('Email Peserta');
            $table->string('training_title', 150)->comment('Jenis Pelatihan');
            $table->foreignId('training_program_id')->nullable()->constrained('training_programs')->nullOnDelete();
            $table->foreignId('training_batch_id')->nullable()->constrained('training_batches')->nullOnDelete();
            $table->date('training_date')->comment('Tanggal Pelaksanaan Pelatihan');
            $table->string('location', 150)->default('Hotel / In-House / Online')->comment('Lokasi Pelatihan');
            $table->decimal('price', 15, 2)->default(0.00)->comment('Harga Biaya Pelatihan');
            $table->enum('source', ['web', 'ig', 'bod', 'other'])->default('web')->comment('Sumber Leads: Web, IG, BOD, Lainnya');
            $table->foreignId('lsp_id')->nullable()->constrained('lsps')->nullOnDelete()->comment('Mitra LSP BNSP');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->comment('Status Pembayaran: Belum Bayar, Sebagian/DP, Lunas');
            $table->enum('certificate_status', ['process', 'passed', 'failed', 'issued'])->default('process')->comment('Status Sertifikat: Proses, Kompeten, Belum Kompeten, Terbit');
            $table->text('notes')->nullable()->comment('Catatan Tambahan');
            $table->timestamps();

            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'payment_status']);
            $table->index(['company_id', 'certificate_status']);
            $table->index(['company_id', 'training_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
