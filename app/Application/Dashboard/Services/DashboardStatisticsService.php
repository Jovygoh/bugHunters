<?php

namespace App\Application\Dashboard\Services;

use App\Domain\Dashboard\Repositories\DashboardRepositoryInterface;

/** Assembles dashboard projection reads without querying transactional models. */
final readonly class DashboardStatisticsService
{
    public function __construct(private DashboardRepositoryInterface $dashboard) {}

    public function summary(string $organizationId, string $metricDate): array
    {
        return [
            'latest' => $this->dashboard->latestDailyMetric($organizationId),
            'departments' => $this->dashboard->departmentRisk($organizationId, $metricDate),
            'ai_tools' => $this->dashboard->aiToolUsage($organizationId, $metricDate),
            'incidents' => $this->dashboard->incidentStatus($organizationId, $metricDate),
            'policy_compliance' => $this->dashboard->policyCompliance($organizationId, $metricDate),
        ];
    }

    public function trend(string $organizationId, string $from, string $to)
    {
        return $this->dashboard->dailyMetrics($organizationId, $from, $to);
    }
}

