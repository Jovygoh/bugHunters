<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classification_levels', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('classification_scheme_id')->constrained()->restrictOnDelete(); $table->string('name',100); $table->string('code',100);
            $table->unsignedSmallInteger('rank'); $table->string('severity',20); $table->char('color',7)->nullable(); $table->text('description')->nullable(); $table->json('handling_rules')->nullable();
            $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->unique(['classification_scheme_id','code']); $table->unique(['classification_scheme_id','rank']); $table->index(['organization_id','severity']);
        });
        Schema::table('organization_ai_tools', function (Blueprint $table): void {
            $table->foreign('allowed_classification_level_id')->references('id')->on('classification_levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organization_ai_tools', function (Blueprint $table): void {
            $table->dropForeign(['allowed_classification_level_id']);
        });
        Schema::dropIfExists('classification_levels');
    }
};
