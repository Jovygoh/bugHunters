<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete(); $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type',100); $table->string('channel',30); $table->boolean('is_enabled')->default(true); $table->string('minimum_severity',20)->nullable(); $table->json('quiet_hours')->nullable();
            $table->timestamps(6); $table->unique(['user_id','notification_type','channel'],'notification_preference_unique'); $table->index(['organization_id','notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};

