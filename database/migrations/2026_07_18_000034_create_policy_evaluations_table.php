<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_evaluations', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('organization_ai_tool_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('data_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('discovery_observation_id')->nullable()->constrained()->nullOnDelete(); $table->string('requested_action',100); $table->string('decision',30);
            $table->string('reason_code',100); $table->decimal('risk_score',5,2)->nullable(); $table->json('context'); $table->binary('context_hash')->nullable(); $table->json('obligations')->nullable();
            $table->uuid('correlation_id'); $table->unsignedInteger('evaluation_duration_us')->nullable(); $table->string('engine_version',50); $table->timestamp('evaluated_at',6); $table->timestamp('created_at',6)->useCurrent();
            $table->unique(['organization_id','correlation_id']); $table->index(['organization_id','evaluated_at']); $table->index(['organization_id','decision','evaluated_at']);
            $table->index(['organization_id','employee_id','evaluated_at'],'policy_eval_employee_idx'); $table->index(['organization_id','organization_ai_tool_id','evaluated_at'],'policy_eval_ai_tool_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_evaluations');
    }
};

