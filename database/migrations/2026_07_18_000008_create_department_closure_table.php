<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_closure', function (Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
                    $table->foreignUuid('ancestor_department_id')->constrained('departments')->cascadeOnDelete();
                    $table->foreignUuid('descendant_department_id')->constrained('departments')->cascadeOnDelete();
                    $table->unsignedSmallInteger('depth'); $table->timestamp('created_at', 6)->useCurrent();
                    $table->unique(['ancestor_department_id','descendant_department_id']);
                    $table->index(['organization_id','descendant_department_id','depth'],'dept_closure_desc_idx');
                    $table->index(['organization_id','ancestor_department_id','depth'],'dept_closure_anc_idx');
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_closure');
    }
};

