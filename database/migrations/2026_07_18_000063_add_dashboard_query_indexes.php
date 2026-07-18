<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', fn (Blueprint $table) =>
            $table->index(['organization_id', 'created_at'], 'employees_org_created_idx'));
        Schema::table('devices', fn (Blueprint $table) =>
            $table->index(['organization_id', 'registration_status', 'last_seen_at'], 'devices_org_active_idx'));
        Schema::table('discovery_findings', fn (Blueprint $table) =>
            $table->index(['organization_id', 'finding_type', 'status'], 'findings_org_type_status_idx'));
        Schema::table('incidents', fn (Blueprint $table) =>
            $table->index(['organization_id', 'severity', 'status', 'detected_at'], 'incidents_org_risk_trend_idx'));
    }

    public function down(): void
    {
        Schema::table('employees', fn (Blueprint $table) => $table->dropIndex('employees_org_created_idx'));
        Schema::table('devices', fn (Blueprint $table) => $table->dropIndex('devices_org_active_idx'));
        Schema::table('discovery_findings', fn (Blueprint $table) => $table->dropIndex('findings_org_type_status_idx'));
        Schema::table('incidents', fn (Blueprint $table) => $table->dropIndex('incidents_org_risk_trend_idx'));
    }
};
