<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_compliance_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete(); $table->foreignUuid('policy_id')->constrained()->cascadeOnDelete(); $table->date('metric_date');
            $table->unsignedBigInteger('evaluation_count')->default(0); $table->unsignedBigInteger('allow_count')->default(0); $table->unsignedBigInteger('warn_count')->default(0);
            $table->unsignedBigInteger('approval_required_count')->default(0); $table->unsignedBigInteger('block_count')->default(0); $table->decimal('compliance_rate',7,4)->nullable();
            $table->timestamps(6); $table->unique(['policy_id','metric_date']); $table->index(['organization_id','metric_date','compliance_rate'],'policy_compliance_metric_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_compliance_summaries');
    }
};

