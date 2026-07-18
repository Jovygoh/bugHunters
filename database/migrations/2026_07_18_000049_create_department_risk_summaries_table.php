<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_risk_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete(); $table->foreignUuid('department_id')->constrained()->cascadeOnDelete(); $table->date('metric_date');
            $table->unsignedInteger('employee_count')->default(0); $table->unsignedInteger('device_count')->default(0); $table->unsignedInteger('ai_tool_count')->default(0);
            $table->unsignedBigInteger('policy_violation_count')->default(0); $table->unsignedInteger('open_incident_count')->default(0); $table->decimal('risk_score',7,2)->nullable();
            $table->timestamps(6); $table->unique(['department_id','metric_date']); $table->index(['organization_id','metric_date','risk_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_risk_summaries');
    }
};

