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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 30)->unique();
            $table->string('tax_id', 50)->nullable()->comment('NPWP Badan');
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 150)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
