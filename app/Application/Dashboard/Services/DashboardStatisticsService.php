<?php

namespace App\Application\Dashboard\Services;

use App\Domain\Dashboard\Repositories\DashboardRepositoryInterface;
use Carbon\CarbonImmutable;

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

    public function operationalSummary(string $organizationId, int $activeDays = 30): array
    {
        $activeSince = CarbonImmutable::now()->subDays($activeDays)->startOfDay()->toDateTimeString();

        return $this->dashboard->operationalSummary($organizationId, $activeSince);
    }

    public function monthlyStatistics(string $organizationId, int $months = 12): array
    {
        $end = CarbonImmutable::now()->endOfMonth();
        $start = $end->startOfMonth()->subMonths($months - 1);
        $statistics = [];

        for ($period = $start; $period->lte($end); $period = $period->addMonth()) {
            $statistics[$period->format('Y-m-01')] = [
                'period' => $period->format('Y-m-01'),
                'employees' => 0,
                'devices' => 0,
                'ai_detections' => 0,
                'incidents' => 0,
                'policy_violations' => 0,
            ];
        }

        foreach ($this->dashboard->monthlyStatistics($organizationId, $start->toDateTimeString(), $end->toDateTimeString()) as $row) {
            if (isset($statistics[$row->period], $statistics[$row->period][$row->metric])) {
                $statistics[$row->period][$row->metric] = (int) $row->total;
            }
        }

        return array_values($statistics);
    }

    public function incidentTrends(string $organizationId, string $from, string $to, string $interval): array
    {
        return array_map(static fn (object $row): array => [
            'period' => $row->period,
            'severity' => $row->severity,
            'total' => (int) $row->total,
        ], $this->dashboard->incidentTrends($organizationId, $from, $to, $interval));
    }

    public function topViolations(string $organizationId, string $from, string $to, int $limit): array
    {
        return array_map(static fn (object $row): array => [
            'reason_code' => $row->reason_code,
            'decision' => $row->decision,
            'total' => (int) $row->total,
        ], $this->dashboard->topViolations($organizationId, $from, $to, $limit));
    }
}
