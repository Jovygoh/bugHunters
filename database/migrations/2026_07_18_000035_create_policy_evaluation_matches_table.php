<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_evaluation_matches', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('policy_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('policy_id')->constrained()->restrictOnDelete(); $table->foreignUuid('policy_version_id')->constrained()->restrictOnDelete(); $table->foreignUuid('policy_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('matched'); $table->string('effect',30)->nullable(); $table->integer('priority'); $table->json('match_details')->nullable(); $table->timestamp('created_at',6)->useCurrent();
            $table->index(['policy_evaluation_id','matched','priority'],'policy_eval_match_result_idx'); $table->index(['organization_id','policy_id','created_at'],'policy_eval_match_policy_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_evaluation_matches');
    }
};

