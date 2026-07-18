<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discovery_findings', function (Blueprint $table): void {
            $table->string('detected_domain', 253)->nullable();
            $table->index(
                ['organization_id', 'detected_domain', 'status'],
                'discovery_finding_domain_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('discovery_findings', function (Blueprint $table): void {
            $table->dropIndex('discovery_finding_domain_status_idx');
            $table->dropColumn('detected_domain');
        });
    }
};
