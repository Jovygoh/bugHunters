<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Application\Dashboard\Services\DashboardStatisticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Dashboard\DashboardSummaryRequest;
use App\Http\Requests\Api\V1\Dashboard\IncidentTrendRequest;
use App\Http\Requests\Api\V1\Dashboard\MonthlyStatisticsRequest;
use App\Http\Requests\Api\V1\Dashboard\TopViolationRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function __construct(private readonly DashboardStatisticsService $dashboard) {}

    public function summary(DashboardSummaryRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->dashboard->operationalSummary(
            $request->user()->organization_id,
            $request->integer('active_days', 30),
        )]);
    }

    public function monthlyStatistics(MonthlyStatisticsRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->dashboard->monthlyStatistics(
            $request->user()->organization_id,
            $request->integer('months', 12),
        )]);
    }

    public function incidentTrends(IncidentTrendRequest $request): JsonResponse
    {
        [$from, $to] = $this->dateRange($request->input('from'), $request->input('to'));

        return response()->json(['data' => $this->dashboard->incidentTrends(
            $request->user()->organization_id, $from, $to, $request->input('interval', 'day'),
        )]);
    }

    public function topViolations(TopViolationRequest $request): JsonResponse
    {
        [$from, $to] = $this->dateRange($request->input('from'), $request->input('to'));

        return response()->json(['data' => $this->dashboard->topViolations(
            $request->user()->organization_id, $from, $to, $request->integer('limit', 10),
        )]);
    }

    private function dateRange(?string $from, ?string $to): array
    {
        return [
            CarbonImmutable::parse($from ?? '30 days ago')->startOfDay()->toDateTimeString(),
            CarbonImmutable::parse($to ?? 'now')->endOfDay()->toDateTimeString(),
        ];
    }
}
