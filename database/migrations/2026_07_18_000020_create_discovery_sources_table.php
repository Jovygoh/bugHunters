<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discovery_sources', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->string('name',150); $table->string('source_type',50); $table->string('status',30)->default('active');
            $table->string('external_key')->nullable(); $table->json('configuration')->nullable(); $table->string('credential_ref')->nullable();
            $table->timestamp('last_sync_at',6)->nullable(); $table->timestamps(6); $table->softDeletes('deleted_at',6);
            $table->unique(['organization_id','name']); $table->unique(['organization_id','source_type','external_key'],'discovery_source_external_unique'); $table->index(['organization_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_sources');
    }
};

