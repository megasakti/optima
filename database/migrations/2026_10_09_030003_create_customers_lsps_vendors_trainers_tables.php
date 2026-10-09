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
        // 1. Customers (B2B, B2G, B2C)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 30)->index();
            $table->string('name', 150);
            $table->enum('customer_type', ['corporate', 'government', 'individual', 'institution'])->default('corporate');
            $table->string('pic_name', 100)->nullable();
            $table->string('pic_phone', 50)->nullable();
            $table->string('pic_email', 100)->nullable();
            $table->string('tax_id', 50)->nullable()->comment('NPWP Klien');
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->integer('payment_term_days')->default(30);
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // 2. LSP (Lembaga Sertifikasi Profesi mitra BNSP)
        Schema::create('lsps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 30)->index();
            $table->string('name', 150);
            $table->string('bnsp_license_number', 100)->nullable()->comment('No Lisensi BNSP');
            $table->string('pic_name', 100)->nullable();
            $table->string('pic_phone', 50)->nullable();
            $table->string('pic_email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // 3. Vendors (Hotel, Transport, ATK, Souvenir, etc.)
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 30)->index();
            $table->string('name', 150);
            $table->enum('category', ['hotel_venue', 'catering', 'stationery_printing', 'transport', 'it_software', 'general'])->default('general');
            $table->string('pic_name', 100)->nullable();
            $table->string('pic_phone', 50)->nullable();
            $table->string('pic_email', 100)->nullable();
            $table->string('tax_id', 50)->nullable()->comment('NPWP Vendor');
            $table->text('address')->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder', 100)->nullable();
            $table->integer('payment_term_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // 4. Trainers / Instruktur Pelatihan
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 30)->index();
            $table->string('name', 150);
            $table->string('specialization', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('tax_id', 50)->nullable()->comment('NPWP Instruktur');
            $table->string('id_card_number', 30)->nullable()->comment('NIK KTP');
            $table->decimal('default_daily_rate', 15, 2)->default(0.00);
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('lsps');
        Schema::dropIfExists('customers');
    }
};
