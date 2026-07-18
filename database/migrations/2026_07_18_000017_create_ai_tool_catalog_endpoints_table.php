<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tool_catalog_endpoints', function (Blueprint $table): void {
                    $table->uuid('id')->primary(); $table->foreignUuid('ai_tool_id')->constrained('ai_tool_catalog')->cascadeOnDelete();
                    $table->string('endpoint_type',30); $table->string('value',2048); $table->string('normalized_value',2048); $table->binary('normalized_value_hash', 32, true);
                    $table->boolean('is_primary')->default(false); $table->timestamps(6); $table->softDeletes('deleted_at',6);
                    $table->index(['endpoint_type','normalized_value_hash'], 'catalog_endpoint_lookup_idx');
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tool_catalog_endpoints');
    }
};
