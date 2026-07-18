<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('recipient_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('notification_type',150); $table->string('severity',20)->nullable(); $table->string('title'); $table->text('body')->nullable(); $table->json('data')->nullable();
            $table->foreignUuid('incident_id')->nullable()->constrained()->nullOnDelete(); $table->foreignUuid('policy_evaluation_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('read_at',6)->nullable(); $table->timestamp('expires_at',6)->nullable(); $table->timestamps(6); $table->softDeletes('deleted_at',6);
            $table->index(['recipient_user_id','read_at','created_at'],'notification_recipient_read_idx'); $table->index(['organization_id','notification_type','created_at'],'notification_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

