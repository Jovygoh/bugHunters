<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->uuid('active_version_id')->nullable();
            $table->string('name',200); $table->string('code',100); $table->text('description')->nullable(); $table->string('category',100); $table->string('status',30)->default('draft');
            $table->integer('priority')->default(0); $table->boolean('is_mandatory')->default(false); $table->string('default_effect',30)->default('block');
            $table->timestamp('effective_from',6)->nullable(); $table->timestamp('effective_until',6)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->unique(['organization_id','code']); $table->index(['organization_id','status','priority']); $table->index(['organization_id','effective_from','effective_until'],'policy_effective_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};

