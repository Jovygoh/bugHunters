<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discovery_observations', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('discovery_scan_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('discovery_source_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('organization_ai_tool_id')->nullable()->constrained()->nullOnDelete(); $table->string('external_event_id')->nullable();
            $table->string('observation_type',50); $table->string('destination_domain',253)->nullable(); $table->binary('destination_url_hash')->nullable();
            $table->string('application_name')->nullable(); $table->string('action',50)->nullable(); $table->unsignedBigInteger('bytes_sent')->nullable(); $table->unsignedBigInteger('bytes_received')->nullable();
            $table->string('classification_hint',100)->nullable(); $table->decimal('matched_confidence',5,4)->nullable(); $table->decimal('risk_score',5,2)->nullable();
            $table->string('raw_payload_ref',500)->nullable(); $table->json('normalized_attributes')->nullable();
            $table->timestamp('observed_at',6); $table->timestamp('ingested_at',6); $table->timestamp('created_at',6)->useCurrent();
            $table->unique(['discovery_source_id','external_event_id']); $table->index(['organization_id','observed_at']);
            $table->index(['organization_id','employee_id','observed_at'],'observation_employee_idx'); $table->index(['organization_id','device_id','observed_at'],'observation_device_idx');
            $table->index(['organization_id','organization_ai_tool_id','observed_at'],'observation_ai_tool_idx'); $table->index(['organization_id','destination_domain','observed_at'],'observation_domain_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_observations');
    }
};

