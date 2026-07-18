<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tool_catalog', function (Blueprint $table): void {
                    $table->uuid('id')->primary(); $table->foreignUuid('vendor_id')->nullable()->constrained('ai_tool_vendors')->nullOnDelete();
                    $table->string('name',200); $table->string('slug',150)->unique(); $table->text('description')->nullable();
                    $table->string('category',100); $table->string('delivery_model',50)->nullable(); $table->string('default_risk_level',20)->default('unknown');
                    $table->boolean('stores_prompts')->nullable(); $table->boolean('trains_on_customer_data')->nullable(); $table->boolean('supports_enterprise_controls')->nullable();
                    $table->json('data_residency')->nullable(); $table->json('security_attributes')->nullable(); $table->json('metadata')->nullable();
                    $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->index(['category','default_risk_level']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tool_catalog');
    }
};

