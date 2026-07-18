<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_status_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete(); $table->date('metric_date');
            $table->string('status',30); $table->string('severity',20); $table->unsignedInteger('incident_count')->default(0); $table->unsignedBigInteger('average_resolution_seconds')->nullable();
            $table->timestamps(6); $table->unique(['organization_id','metric_date','status','severity'],'incident_status_metric_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_status_summaries');
    }
};

