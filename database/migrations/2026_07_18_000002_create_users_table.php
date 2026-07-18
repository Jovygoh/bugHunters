<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
                    $table->string('name', 200);
                    $table->string('email', 320);
                    $table->string('normalized_email', 320);
                    $table->string('password')->nullable();
                    $table->string('status', 30)->default('active');
                    $table->timestamp('email_verified_at', 6)->nullable();
                    $table->timestamp('last_login_at', 6)->nullable();
                    $table->binary('last_login_ip')->nullable();
                    $table->timestamp('password_changed_at', 6)->nullable();
                    $table->boolean('must_change_password')->default(false);
                    $table->rememberToken();
                    $table->json('settings')->nullable();
                    $table->timestamps(6); $table->softDeletes('deleted_at', 6);
                    $table->unique(['organization_id','normalized_email']);
                    $table->index(['organization_id','status']);
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

