<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policy_scopes', function (Blueprint $table): void {
            $table->foreignUuid('role_id')->nullable()->constrained()->cascadeOnDelete();
            $table->index(
                ['organization_id', 'role_id', 'scope_effect'],
                'policy_scope_role_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('policy_scopes', function (Blueprint $table): void {
            $table->dropIndex('policy_scope_role_idx');
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
