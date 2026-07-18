<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discovery_scans', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('discovery_source_id')->constrained()->restrictOnDelete(); $table->foreignUuid('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status',30)->default('queued'); $table->string('scan_type',50); $table->timestamp('started_at',6)->nullable(); $table->timestamp('completed_at',6)->nullable();
            $table->unsignedBigInteger('records_received')->default(0); $table->unsignedBigInteger('records_processed')->default(0);
            $table->unsignedBigInteger('findings_created')->default(0); $table->unsignedInteger('error_count')->default(0);
            $table->json('error_summary')->nullable(); $table->json('parameters')->nullable(); $table->timestamps(6);
            $table->index(['organization_id','status','created_at']); $table->index(['discovery_source_id','started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_scans');
    }
};

