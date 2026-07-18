<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->string('name', 200);
                    $table->string('slug', 100)->unique();
                    $table->string('status', 30)->default('active')->index();
                    $table->string('default_timezone', 64)->default('UTC');
                    $table->string('default_locale', 10)->default('en');
                    $table->json('settings')->nullable();
                    $table->json('retention_settings')->nullable();
                    $table->timestamps(6);
                    $table->softDeletes('deleted_at', 6);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};

