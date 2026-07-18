<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_evidence', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('incident_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('evidence_type',50); $table->string('name'); $table->text('description')->nullable();
            $table->string('storage_disk',50); $table->string('storage_path',1000); $table->string('mime_type',150)->nullable(); $table->unsignedBigInteger('size_bytes')->nullable();
            $table->binary('sha256'); $table->string('encryption_key_ref')->nullable(); $table->timestamp('captured_at',6)->nullable(); $table->timestamps(6); $table->softDeletes('deleted_at',6);
            $table->index(['incident_id','created_at']); $table->index(['organization_id','sha256']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_evidence');
    }
};

