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
        Schema::create('deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('model_type', 100)->index();
            $table->unsignedBigInteger('model_id')->index();
            $table->string('model_label', 150)->comment('Nama atau identitas data');
            $table->text('reason')->comment('Alasan penghapusan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deletion_requests');
    }
};
