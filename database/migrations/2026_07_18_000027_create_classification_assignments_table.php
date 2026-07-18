<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classification_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('data_asset_id')->constrained()->restrictOnDelete(); $table->foreignUuid('classification_level_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('assignment_source',50); $table->decimal('confidence',5,4)->nullable();
            $table->text('reason')->nullable(); $table->json('evidence')->nullable(); $table->timestamp('effective_at',6); $table->timestamp('superseded_at',6)->nullable(); $table->timestamp('created_at',6)->useCurrent();
            $table->index(['data_asset_id','effective_at']); $table->index(['organization_id','classification_level_id','effective_at'],'classification_assignment_level_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classification_assignments');
    }
};

