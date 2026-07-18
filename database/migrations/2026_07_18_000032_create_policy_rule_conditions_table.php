<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_rule_conditions', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('policy_rule_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('condition_group')->default(1); $table->unsignedSmallInteger('sequence'); $table->string('attribute',150); $table->string('operator',50);
            $table->string('value_type',30); $table->json('value'); $table->boolean('negated')->default(false); $table->timestamps(6);
            $table->unique(['policy_rule_id','condition_group','sequence'],'policy_condition_sequence_unique'); $table->index(['organization_id','attribute','operator'],'policy_condition_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_rule_conditions');
    }
};

