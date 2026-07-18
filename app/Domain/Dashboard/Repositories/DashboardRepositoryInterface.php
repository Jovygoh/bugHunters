<?php

namespace App\Domain\Dashboard\Repositories;

use App\Models\DashboardDailyMetric;
use Illuminate\Database\Eloquent\Collection;

interface DashboardRepositoryInterface
{
    public function latestDailyMetric(string $organizationId): ?DashboardDailyMetric;

    public function dailyMetrics(string $organizationId, string $from, string $to): Collection;

    public function departmentRisk(string $organizationId, string $metricDate): Collection;

    public function aiToolUsage(string $organizationId, string $metricDate): Collection;

    public function incidentStatus(string $organizationId, string $metricDate): Collection;

    public function policyCompliance(string $organizationId, string $metricDate): Collection;
}

