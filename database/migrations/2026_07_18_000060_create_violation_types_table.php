<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->string('code', 100);
            $table->string('severity', 20);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);

            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_types');
    }
};
