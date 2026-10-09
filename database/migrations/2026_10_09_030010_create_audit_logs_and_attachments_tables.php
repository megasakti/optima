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
        // 1. Audit Logs (Jejak Rekam Finansial & Keamanan)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('auditable');
            $table->string('action', 50)->comment('create, update, delete, post, reverse, approve, cancel');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'action']);
            $table->index('created_at');
        });

        // 2. Attachments (Bukti Transfer, Kontrak SPK, Invoice Vendor/LSP)
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->nullableMorphs('attachable');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0)->comment('Ukuran dalam byte');
            $table->string('caption', 255)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('audit_logs');
    }
};
