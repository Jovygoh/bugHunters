<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type',50); $table->string('actor_identifier')->nullable(); $table->string('action',150); $table->string('auditable_type',150)->nullable(); $table->uuid('auditable_id')->nullable();
            $table->string('outcome',30); $table->string('description',1000)->nullable(); $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->json('changed_fields')->nullable();
            $table->uuid('request_id')->nullable(); $table->uuid('correlation_id')->nullable(); $table->string('source',50); $table->string('http_method',10)->nullable(); $table->string('request_path',2048)->nullable();
            $table->binary('ip_address')->nullable(); $table->string('user_agent',1000)->nullable(); $table->json('metadata')->nullable(); $table->binary('previous_hash')->nullable(); $table->binary('entry_hash')->nullable();
            $table->timestamp('occurred_at',6); $table->timestamp('created_at',6)->useCurrent(); $table->index(['organization_id','occurred_at']); $table->index(['organization_id','actor_user_id','occurred_at'],'audit_actor_occurred_idx');
            $table->index(['organization_id','action','occurred_at']); $table->index(['organization_id','auditable_type','auditable_id','occurred_at'],'audit_auditable_idx'); $table->index(['organization_id','correlation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

