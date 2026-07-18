<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_ai_tools', function (Blueprint $table): void {
            $table->string('primary_domain', 253)->nullable();
            $table->string('category', 100)->nullable();
            $table->text('description')->nullable();
            $table->index(['organization_id', 'primary_domain'], 'org_ai_tool_primary_domain_idx');
        });
    }

    public function down(): void
    {
        Schema::table('organization_ai_tools', function (Blueprint $table): void {
            $table->dropIndex('org_ai_tool_primary_domain_idx');
            $table->dropColumn(['primary_domain', 'category', 'description']);
        });
    }
};
