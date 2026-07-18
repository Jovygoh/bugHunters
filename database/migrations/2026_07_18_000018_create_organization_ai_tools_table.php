<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_ai_tools', function (Blueprint $table): void {
                    $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
                    $table->foreignUuid('catalog_ai_tool_id')->nullable()->constrained('ai_tool_catalog')->nullOnDelete();
                    $table->string('display_name',200); $table->string('status',30)->default('discovered'); $table->string('approval_status',30)->default('unreviewed');
                    $table->string('risk_level',20)->default('unknown'); $table->decimal('risk_score',5,2)->nullable();
                    $table->timestamp('approved_at',6)->nullable(); $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamp('blocked_at',6)->nullable(); $table->foreignUuid('blocked_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamp('reviewed_at',6)->nullable(); $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->uuid('allowed_classification_level_id')->nullable(); $table->json('settings')->nullable(); $table->json('risk_assessment')->nullable();
                    $table->timestamps(6); $table->softDeletes('deleted_at',6);
                    $table->unique(['organization_id','catalog_ai_tool_id']); $table->index(['organization_id','approval_status','risk_level'],'org_ai_approval_risk_idx'); $table->index(['organization_id','status']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_ai_tools');
    }
};

