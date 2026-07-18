<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_posture_snapshots', function (Blueprint $table): void {
                    $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
                    $table->foreignUuid('device_id')->constrained()->restrictOnDelete(); $table->string('source',100);
                    $table->string('compliance_status',30); $table->decimal('risk_score',5,2)->nullable();
                    $table->boolean('is_encrypted')->nullable(); $table->boolean('has_screen_lock')->nullable();
                    $table->boolean('has_endpoint_agent')->nullable(); $table->boolean('is_os_supported')->nullable();
                    $table->json('attributes')->nullable(); $table->timestamp('observed_at',6); $table->timestamp('created_at',6)->useCurrent();
                    $table->index(['organization_id','device_id','observed_at'],'device_posture_observed_idx');
                    $table->index(['organization_id','compliance_status','observed_at'],'device_posture_compliance_idx');
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_posture_snapshots');
    }
};

