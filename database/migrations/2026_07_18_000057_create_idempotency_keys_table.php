<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete(); $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('idempotency_key'); $table->string('request_method',10); $table->string('request_path',2048); $table->binary('request_hash'); $table->string('status',30)->default('processing');
            $table->unsignedSmallInteger('response_status')->nullable(); $table->mediumText('response_body')->nullable(); $table->timestamp('locked_until',6)->nullable(); $table->timestamp('expires_at',6);
            $table->timestamps(6); $table->unique(['organization_id','idempotency_key']); $table->index('expires_at'); $table->index(['status','locked_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};

