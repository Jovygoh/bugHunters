<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_scopes', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('policy_id')->constrained()->cascadeOnDelete();
            $table->string('scope_effect',20)->default('include'); $table->foreignUuid('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->cascadeOnDelete(); $table->foreignUuid('device_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('organization_ai_tool_id')->nullable()->constrained()->cascadeOnDelete(); $table->foreignUuid('classification_level_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('includes_descendants')->default(false); $table->timestamps(6);
            $table->index(['organization_id','department_id','scope_effect'],'policy_scope_department_idx'); $table->index(['organization_id','employee_id','scope_effect'],'policy_scope_employee_idx');
            $table->index(['organization_id','device_id','scope_effect'],'policy_scope_device_idx'); $table->index(['organization_id','organization_ai_tool_id','scope_effect'],'policy_scope_ai_tool_idx');
            $table->index(['organization_id','classification_level_id','scope_effect'],'policy_scope_classification_idx'); $table->index(['policy_id','scope_effect']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_scopes');
    }
};

