<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_daily_metrics', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete(); $table->date('metric_date');
            $table->unsignedInteger('active_employees')->default(0); $table->unsignedInteger('registered_devices')->default(0); $table->unsignedInteger('noncompliant_devices')->default(0);
            $table->unsignedInteger('discovered_ai_tools')->default(0); $table->unsignedInteger('blocked_ai_tools')->default(0); $table->unsignedBigInteger('policy_evaluations')->default(0);
            $table->unsignedBigInteger('blocked_evaluations')->default(0); $table->unsignedInteger('open_incidents')->default(0); $table->unsignedInteger('critical_incidents')->default(0);
            $table->decimal('risk_score',7,2)->nullable(); $table->timestamps(6); $table->unique(['organization_id','metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_daily_metrics');
    }
};

