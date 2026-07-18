<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->string('tokenable_type'); $table->uuid('tokenable_id');
                    $table->string('name'); $table->string('token', 64)->unique(); $table->json('abilities')->nullable();
                    $table->timestamp('last_used_at', 6)->nullable(); $table->timestamp('expires_at', 6)->nullable()->index();
                    $table->timestamps(6); $table->index(['tokenable_type','tokenable_id']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

