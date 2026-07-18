<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tool_vendors', function (Blueprint $table): void {
                    $table->uuid('id')->primary(); $table->string('name',200); $table->string('normalized_name',200)->unique();
                    $table->string('website_url',2048)->nullable(); $table->string('privacy_url',2048)->nullable(); $table->string('terms_url',2048)->nullable();
                    $table->char('country_code',2)->nullable(); $table->json('metadata')->nullable(); $table->timestamps(6); $table->softDeletes('deleted_at',6);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tool_vendors');
    }
};

