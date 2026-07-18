<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
                    $table->foreignUuid('parent_department_id')->nullable()->constrained('departments')->nullOnDelete();
                    $table->uuid('manager_employee_id')->nullable();
                    $table->string('name', 200); $table->string('code', 100);
                    $table->text('description')->nullable(); $table->string('status', 30)->default('active');
                    $table->json('metadata')->nullable(); $table->timestamps(6); $table->softDeletes('deleted_at', 6);
                    $table->unique(['organization_id','code']);
                    $table->index(['organization_id','parent_department_id']);
                    $table->index(['organization_id','status','deleted_at']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

