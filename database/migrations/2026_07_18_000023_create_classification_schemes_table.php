<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classification_schemes', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->string('name',150); $table->text('description')->nullable(); $table->string('status',30)->default('active'); $table->boolean('is_default')->default(false);
            $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->unique(['organization_id','name']); $table->index(['organization_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classification_schemes');
    }
};

