<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('notification_id')->constrained()->cascadeOnDelete();
            $table->string('channel',30); $table->binary('destination_hash')->nullable(); $table->string('status',30)->default('pending'); $table->string('provider_message_id')->nullable();
            $table->unsignedSmallInteger('attempt_count')->default(0); $table->timestamp('last_attempt_at',6)->nullable(); $table->timestamp('delivered_at',6)->nullable(); $table->timestamp('failed_at',6)->nullable();
            $table->string('failure_code',100)->nullable(); $table->string('failure_message',1000)->nullable(); $table->timestamps(6); $table->unique(['notification_id','channel']);
            $table->index(['status','last_attempt_at']); $table->index(['organization_id','channel','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};

