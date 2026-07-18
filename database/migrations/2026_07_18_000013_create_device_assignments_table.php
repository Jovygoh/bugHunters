<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_assignments', function (Blueprint $table): void {
                    $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
                    $table->foreignUuid('device_id')->constrained()->restrictOnDelete(); $table->foreignUuid('employee_id')->constrained()->restrictOnDelete();
                    $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamp('assigned_at',6); $table->timestamp('unassigned_at',6)->nullable();
                    $table->string('assignment_type',30)->default('primary'); $table->string('reason',500)->nullable(); $table->timestamps(6);
                    $table->index(['organization_id','employee_id','unassigned_at'],'device_assign_employee_idx');
                    $table->index(['device_id','assigned_at']); $table->index(['device_id','unassigned_at']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_assignments');
    }
};

