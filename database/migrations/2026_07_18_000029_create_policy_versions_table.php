<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('policy_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version_number'); $table->string('status',30)->default('draft'); $table->string('definition_schema',30)->default('1.0');
            $table->json('definition'); $table->binary('definition_hash'); $table->text('change_summary')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignUuid('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at',6)->nullable(); $table->timestamp('retired_at',6)->nullable(); $table->timestamps(6);
            $table->unique(['policy_id','version_number']); $table->index(['organization_id','status','published_at']);
        });
        Schema::table('policies', function (Blueprint $table): void {
            $table->foreign('active_version_id')->references('id')->on('policy_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table): void {
            $table->dropForeign(['active_version_id']);
        });
        Schema::dropIfExists('policy_versions');
    }
};
