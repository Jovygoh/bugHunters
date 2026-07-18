<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_ai_tool_endpoints', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('organization_ai_tool_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint_type',30); $table->string('value',2048); $table->string('normalized_value',2048); $table->binary('normalized_value_hash', 32, true); $table->string('match_mode',30)->default('exact');
            $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->index(['organization_id','endpoint_type','normalized_value_hash'], 'org_ai_endpoint_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_ai_tool_endpoints');
    }
};
