<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_events', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('incident_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('event_type',50); $table->string('from_status',30)->nullable(); $table->string('to_status',30)->nullable();
            $table->string('from_severity',20)->nullable(); $table->string('to_severity',20)->nullable(); $table->json('payload')->nullable(); $table->timestamp('occurred_at',6); $table->timestamp('created_at',6)->useCurrent();
            $table->index(['incident_id','occurred_at']); $table->index(['organization_id','event_type','occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_events');
    }
};

