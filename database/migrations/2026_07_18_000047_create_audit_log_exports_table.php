<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log_exports', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status',30)->default('queued'); $table->string('format',20); $table->json('filters'); $table->string('storage_disk',50)->nullable(); $table->string('storage_path',1000)->nullable();
            $table->binary('sha256')->nullable(); $table->unsignedBigInteger('record_count')->nullable(); $table->timestamp('started_at',6)->nullable(); $table->timestamp('completed_at',6)->nullable();
            $table->timestamp('expires_at',6)->nullable(); $table->string('failure_message',1000)->nullable(); $table->timestamps(6); $table->index(['organization_id','status','created_at']); $table->index(['requested_by','created_at']); $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log_exports');
    }
};

