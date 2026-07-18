<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
                    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
                    $table->foreignUuid('role_id')->constrained()->cascadeOnDelete();
                    $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamp('expires_at', 6)->nullable(); $table->timestamps(6);
                    $table->unique(['organization_id','user_id','role_id']); $table->index(['user_id','expires_at']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};

