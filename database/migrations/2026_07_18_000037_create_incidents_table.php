<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->unsignedBigInteger('incident_number');
            $table->string('title'); $table->text('description')->nullable(); $table->string('incident_type',50); $table->string('severity',20); $table->string('status',30)->default('open'); $table->string('priority',20);
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('organization_ai_tool_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('policy_evaluation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('discovery_finding_id')->nullable()->constrained('discovery_findings')->nullOnDelete(); $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_team',100)->nullable(); $table->foreignUuid('reported_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('source',50); $table->decimal('risk_score',5,2)->nullable();
            $table->timestamp('detected_at',6); $table->timestamp('acknowledged_at',6)->nullable(); $table->timestamp('resolved_at',6)->nullable(); $table->timestamp('closed_at',6)->nullable();
            $table->string('resolution_code',50)->nullable(); $table->text('resolution_summary')->nullable(); $table->timestamp('sla_due_at',6)->nullable(); $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->unique(['organization_id','incident_number']); $table->index(['organization_id','status','severity']);
            $table->index(['organization_id','assigned_to','status']); $table->index(['organization_id','employee_id','created_at']); $table->index(['organization_id','organization_ai_tool_id','created_at'],'incident_ai_tool_created_idx'); $table->index(['organization_id','sla_due_at','status']);
        });
        Schema::table('discovery_findings', function (Blueprint $table): void {
            $table->foreign('incident_id')->references('id')->on('incidents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('discovery_findings', function (Blueprint $table): void {
            $table->dropForeign(['incident_id']);
        });
        Schema::dropIfExists('incidents');
    }
};
