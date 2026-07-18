<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table): void {
                    $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
                    $table->foreignUuid('current_employee_id')->nullable()->constrained('employees')->nullOnDelete();
                    $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
                    $table->string('device_uuid')->nullable(); $table->string('hostname')->nullable(); $table->string('serial_number')->nullable();
                    $table->string('asset_tag',100)->nullable(); $table->string('device_type',50);
                    $table->string('operating_system',100)->nullable(); $table->string('os_version',100)->nullable();
                    $table->string('ownership_type',30); $table->string('registration_status',30)->default('pending');
                    $table->string('compliance_status',30)->default('unknown'); $table->string('trust_level',30)->default('unknown');
                    $table->timestamp('registered_at',6)->nullable(); $table->timestamp('verified_at',6)->nullable();
                    $table->timestamp('last_seen_at',6)->nullable(); $table->timestamp('revoked_at',6)->nullable(); $table->timestamp('retired_at',6)->nullable();
                    $table->json('metadata')->nullable(); $table->timestamps(6); $table->softDeletes('deleted_at',6);
                    $table->unique(['organization_id','device_uuid']); $table->unique(['organization_id','serial_number']); $table->unique(['organization_id','asset_tag']);
                    $table->index(['organization_id','current_employee_id','registration_status'],'devices_employee_status_idx');
                    $table->index(['organization_id','department_id','compliance_status'],'devices_dept_compliance_idx');
                    $table->index(['organization_id','last_seen_at']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};

