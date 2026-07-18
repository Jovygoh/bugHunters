<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_assets', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('owner_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('current_classification_level_id')->nullable()->constrained('classification_levels')->nullOnDelete();
            $table->string('external_id')->nullable(); $table->string('source_system',100)->nullable(); $table->string('asset_type',50); $table->string('name');
            $table->string('location',2048)->nullable(); $table->binary('location_hash')->nullable(); $table->string('status',30)->default('active');
            $table->boolean('contains_personal_data')->nullable(); $table->boolean('contains_regulated_data')->nullable(); $table->json('metadata')->nullable(); $table->timestamp('last_scanned_at',6)->nullable();
            $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->unique(['organization_id','source_system','external_id'],'data_asset_external_unique');
            $table->index(['organization_id','current_classification_level_id'],'data_asset_classification_idx'); $table->index(['organization_id','department_id','status']); $table->index(['organization_id','owner_employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_assets');
    }
};

