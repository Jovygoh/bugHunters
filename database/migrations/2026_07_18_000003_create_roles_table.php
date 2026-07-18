<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('organization_id')->nullable()->constrained()->cascadeOnDelete();
                    $table->string('name', 100); $table->string('code', 100);
                    $table->string('description', 500)->nullable();
                    $table->boolean('is_system')->default(false);
                    $table->timestamps(6); $table->softDeletes('deleted_at', 6);
                    $table->unique(['organization_id','code']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

