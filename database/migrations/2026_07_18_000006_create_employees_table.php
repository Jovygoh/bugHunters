<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
                    $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
                    $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
                    $table->foreignUuid('manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
                    $table->string('employee_number', 100)->nullable();
                    $table->string('first_name', 100); $table->string('last_name', 100); $table->string('display_name', 200);
                    $table->string('work_email', 320)->nullable(); $table->string('normalized_work_email', 320)->nullable();
                    $table->string('job_title', 150)->nullable(); $table->string('employment_type', 30)->nullable();
                    $table->string('status', 30)->default('active'); $table->string('risk_level', 20)->default('normal');
                    $table->date('hired_at')->nullable(); $table->timestamp('terminated_at', 6)->nullable();
                    $table->json('metadata')->nullable(); $table->timestamps(6); $table->softDeletes('deleted_at', 6);
                    $table->unique(['organization_id','employee_number']); $table->unique('user_id');
                    $table->index(['organization_id','department_id','status']); $table->index(['organization_id','manager_employee_id']);
                    $table->index(['organization_id','normalized_work_email']); $table->index(['organization_id','risk_level']);
                });
        Schema::table('departments', function (Blueprint $table): void {
            $table->foreign('manager_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->index(['organization_id', 'manager_employee_id']);
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->dropForeign(['manager_employee_id']);
            $table->dropIndex(['organization_id', 'manager_employee_id']);
        });
        Schema::dropIfExists('employees');
    }
};
