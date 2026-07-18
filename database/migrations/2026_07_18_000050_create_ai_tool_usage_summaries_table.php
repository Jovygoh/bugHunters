<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tool_usage_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete(); $table->foreignUuid('organization_ai_tool_id')->constrained()->cascadeOnDelete(); $table->date('metric_date');
            $table->unsignedInteger('unique_employee_count')->default(0); $table->unsignedInteger('unique_device_count')->default(0); $table->unsignedBigInteger('observation_count')->default(0);
            $table->unsignedBigInteger('bytes_sent')->default(0); $table->unsignedBigInteger('violation_count')->default(0); $table->unsignedInteger('incident_count')->default(0);
            $table->timestamps(6); $table->unique(['organization_ai_tool_id','metric_date'],'ai_tool_usage_date_unique'); $table->index(['organization_id','metric_date','observation_count'],'ai_tool_usage_observation_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tool_usage_summaries');
    }
};

