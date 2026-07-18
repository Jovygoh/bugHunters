<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->nullable()->constrained()->restrictOnDelete(); $table->string('aggregate_type',150); $table->uuid('aggregate_id')->nullable();
            $table->string('event_type',200); $table->json('payload'); $table->json('headers')->nullable(); $table->timestamp('occurred_at',6); $table->timestamp('available_at',6);
            $table->timestamp('processed_at',6)->nullable(); $table->unsignedInteger('attempt_count')->default(0); $table->string('last_error',2000)->nullable(); $table->timestamp('created_at',6)->useCurrent();
            $table->index(['processed_at','available_at']); $table->index(['organization_id','occurred_at']); $table->index(['aggregate_type','aggregate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_messages');
    }
};

