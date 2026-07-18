<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Dashboard\Repositories\DashboardRepositoryInterface;
use App\Models\AiToolUsageSummary;
use App\Models\DashboardDailyMetric;
use App\Models\DepartmentRiskSummary;
use App\Models\IncidentStatusSummary;
use App\Models\PolicyComplianceSummary;
use Illuminate\Database\Eloquent\Collection;

final class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function latestDailyMetric(string $organizationId): ?DashboardDailyMetric
    {
        return DashboardDailyMetric::query()->where('organization_id', $organizationId)->latest('metric_date')->first();
    }

    public function dailyMetrics(string $organizationId, string $from, string $to): Collection
    {
        return DashboardDailyMetric::query()->where('organization_id', $organizationId)->whereBetween('metric_date', [$from, $to])->orderBy('metric_date')->get();
    }

    public function departmentRisk(string $organizationId, string $metricDate): Collection
    {
        return DepartmentRiskSummary::query()->where('organization_id', $organizationId)->where('metric_date', $metricDate)->orderByDesc('risk_score')->get();
    }

    public function aiToolUsage(string $organizationId, string $metricDate): Collection
    {
        return AiToolUsageSummary::query()->where('organization_id', $organizationId)->where('metric_date', $metricDate)->orderByDesc('observation_count')->get();
    }

    public function incidentStatus(string $organizationId, string $metricDate): Collection
    {
        return IncidentStatusSummary::query()->where('organization_id', $organizationId)->where('metric_date', $metricDate)->get();
    }

    public function policyCompliance(string $organizationId, string $metricDate): Collection
    {
        return PolicyComplianceSummary::query()->where('organization_id', $organizationId)->where('metric_date', $metricDate)->get();
    }
}

