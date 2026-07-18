<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table): void {
            $table->foreignUuid('policy_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100)->default('unknown');
            $table->json('metadata')->nullable();
            $table->index(
                ['organization_id', 'policy_id', 'detected_at'],
                'incident_policy_detected_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table): void {
            $table->dropIndex('incident_policy_detected_idx');
            $table->dropConstrainedForeignId('policy_id');
            $table->dropColumn(['action', 'metadata']);
        });
    }
};
