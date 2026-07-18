<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('policy_version_id')->constrained()->cascadeOnDelete();
            $table->string('name',200); $table->unsignedInteger('sequence'); $table->string('effect',30); $table->string('condition_mode',10)->default('all');
            $table->string('reason_code',100); $table->string('message',1000)->nullable(); $table->json('obligations')->nullable(); $table->boolean('is_enabled')->default(true);
            $table->timestamps(6); $table->unique(['policy_version_id','sequence']); $table->index(['organization_id','effect']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_rules');
    }
};

