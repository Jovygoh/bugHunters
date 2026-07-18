<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Dashboard\Repositories\DashboardRepositoryInterface;
use App\Models\AiToolUsageSummary;
use App\Models\DashboardDailyMetric;
use App\Models\DepartmentRiskSummary;
use App\Models\IncidentStatusSummary;
use App\Models\PolicyComplianceSummary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    public function operationalSummary(string $organizationId, string $activeSince): array
    {
        $row = DB::selectOne(
            <<<'SQL'
            SELECT
                (SELECT COUNT(*) FROM employees
                 WHERE organization_id = ? AND deleted_at IS NULL) AS total_employees,
                (SELECT COUNT(*) FROM devices
                 WHERE organization_id = ? AND deleted_at IS NULL
                   AND registration_status = 'verified' AND last_seen_at >= ?) AS active_devices,
                (SELECT COUNT(DISTINCT employee_id) FROM discovery_observations
                 WHERE organization_id = ? AND employee_id IS NOT NULL AND observed_at >= ?) AS active_ai_users,
                (SELECT COUNT(*) FROM organization_ai_tools
                 WHERE organization_id = ? AND deleted_at IS NULL
                   AND approval_status = 'approved') AS approved_ai_tools,
                (SELECT COUNT(*) FROM discovery_findings
                 WHERE organization_id = ? AND deleted_at IS NULL
                   AND finding_type = 'unknown_ai_tool'
                   AND status IN ('open', 'investigating')) AS unknown_ai_tools,
                (SELECT COUNT(*) FROM incidents
                 WHERE organization_id = ? AND deleted_at IS NULL
                   AND severity IN ('high', 'critical')
                   AND status NOT IN ('resolved', 'closed')) AS high_risk_incidents
            SQL,
            [
                $organizationId,
                $organizationId, $activeSince,
                $organizationId, $activeSince,
                $organizationId,
                $organizationId,
                $organizationId,
            ],
        );

        return array_map('intval', (array) $row);
    }

    public function monthlyStatistics(string $organizationId, string $from, string $to): array
    {
        return DB::select(
            <<<'SQL'
            SELECT DATE_FORMAT(created_at, '%Y-%m-01') AS period, 'employees' AS metric, COUNT(*) AS total
            FROM employees
            WHERE organization_id = ? AND deleted_at IS NULL AND created_at BETWEEN ? AND ?
            GROUP BY period
            UNION ALL
            SELECT DATE_FORMAT(registered_at, '%Y-%m-01'), 'devices', COUNT(*)
            FROM devices
            WHERE organization_id = ? AND deleted_at IS NULL AND registered_at BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(registered_at, '%Y-%m-01')
            UNION ALL
            SELECT DATE_FORMAT(observed_at, '%Y-%m-01'), 'ai_detections', COUNT(*)
            FROM discovery_observations
            WHERE organization_id = ? AND observed_at BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(observed_at, '%Y-%m-01')
            UNION ALL
            SELECT DATE_FORMAT(detected_at, '%Y-%m-01'), 'incidents', COUNT(*)
            FROM incidents
            WHERE organization_id = ? AND deleted_at IS NULL AND detected_at BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(detected_at, '%Y-%m-01')
            UNION ALL
            SELECT DATE_FORMAT(evaluated_at, '%Y-%m-01'), 'policy_violations', COUNT(*)
            FROM policy_evaluations
            WHERE organization_id = ? AND decision <> 'allow' AND evaluated_at BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(evaluated_at, '%Y-%m-01')
            ORDER BY period
            SQL,
            [
                $organizationId, $from, $to,
                $organizationId, $from, $to,
                $organizationId, $from, $to,
                $organizationId, $from, $to,
                $organizationId, $from, $to,
            ],
        );
    }

    public function incidentTrends(string $organizationId, string $from, string $to, string $interval): array
    {
        $format = $interval === 'month' ? '%Y-%m-01' : '%Y-%m-%d';

        return DB::table('incidents')
            ->selectRaw("DATE_FORMAT(detected_at, '{$format}') AS period, severity, COUNT(*) AS total")
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at')
            ->whereBetween('detected_at', [$from, $to])
            ->groupByRaw("DATE_FORMAT(detected_at, '{$format}'), severity")
            ->orderBy('period')
            ->get()
            ->all();
    }

    public function topViolations(string $organizationId, string $from, string $to, int $limit): array
    {
        return DB::table('policy_evaluations')
            ->selectRaw('reason_code, decision, COUNT(*) AS total')
            ->where('organization_id', $organizationId)
            ->where('decision', '<>', 'allow')
            ->whereBetween('evaluated_at', [$from, $to])
            ->groupBy('reason_code', 'decision')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->all();
    }
}
