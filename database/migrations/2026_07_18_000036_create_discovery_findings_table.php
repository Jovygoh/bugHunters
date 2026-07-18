<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discovery_findings', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('observation_id')->nullable()->constrained('discovery_observations')->nullOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('device_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('organization_ai_tool_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('incident_id')->nullable(); $table->string('finding_type',50); $table->string('severity',20); $table->string('status',30)->default('open'); $table->string('title');
            $table->text('description')->nullable(); $table->decimal('risk_score',5,2)->nullable(); $table->timestamp('first_observed_at',6); $table->timestamp('last_observed_at',6); $table->unsignedBigInteger('occurrence_count')->default(1);
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete(); $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at',6)->nullable(); $table->string('resolution_code',50)->nullable(); $table->text('resolution_notes')->nullable(); $table->binary('deduplication_key')->nullable();
            $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->unique(['organization_id','deduplication_key']); $table->index(['organization_id','status','severity']);
            $table->index(['organization_id','employee_id','status']); $table->index(['organization_id','organization_ai_tool_id','status'],'finding_ai_tool_status_idx'); $table->index(['organization_id','last_observed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_findings');
    }
};

