<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Discovery\Repositories\DiscoveryRepositoryInterface;
use App\Models\DiscoveryFinding;
use App\Models\DiscoveryObservation;
use App\Models\DiscoveryScan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentDiscoveryRepository extends AbstractEloquentRepository implements DiscoveryRepositoryInterface
{
    public function __construct(DiscoveryFinding $model) { parent::__construct($model); }

    public function createScan(array $attributes): DiscoveryScan { return DiscoveryScan::query()->create($attributes); }

    public function createObservation(array $attributes): DiscoveryObservation { return DiscoveryObservation::query()->create($attributes); }

    public function upsertObservation(array $identity, array $attributes): DiscoveryObservation
    {
        return DiscoveryObservation::query()->updateOrCreate($identity, $attributes);
    }

    public function paginateObservations(string $organizationId, array $filters = [], int $perPage = 100): LengthAwarePaginator
    {
        $query = DiscoveryObservation::query()->where('organization_id', $organizationId);
        foreach (array_intersect_key($filters, array_flip(['discovery_scan_id', 'discovery_source_id', 'employee_id', 'device_id', 'organization_ai_tool_id', 'observation_type'])) as $column => $value) {
            $query->where($column, $value);
        }
        return $query->orderByDesc('observed_at')->paginate($perPage);
    }

    public function findFindingForOrganization(string $organizationId, string $findingId): ?DiscoveryFinding
    {
        return DiscoveryFinding::query()
            ->with(['employee', 'device', 'organizationAiTool', 'incident'])
            ->where('organization_id', $organizationId)
            ->whereKey($findingId)
            ->first();
    }

    public function findByDeduplicationKey(string $organizationId, string $deduplicationKey): ?DiscoveryFinding
    {
        return DiscoveryFinding::query()
            ->where('organization_id', $organizationId)
            ->where('deduplication_key', $deduplicationKey)
            ->first();
    }

    public function paginateFindings(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = DiscoveryFinding::query()
            ->with(['employee', 'device'])
            ->where('organization_id', $organizationId)
            ->where('finding_type', 'unknown_ai_tool');

        foreach (array_intersect_key($filters, array_flip(['employee_id', 'device_id', 'status', 'severity'])) as $column => $value) {
            $query->where($column, $value);
        }

        if (! empty($filters['domain'])) {
            $query->where('detected_domain', $filters['domain']);
        }

        if (isset($filters['detected_from'])) {
            $query->where('last_observed_at', '>=', $filters['detected_from']);
        }

        if (isset($filters['detected_to'])) {
            $query->where('last_observed_at', '<=', $filters['detected_to']);
        }

        return $query->orderByDesc('last_observed_at')->paginate($perPage);
    }

    protected function filterable(): array { return ['organization_id', 'employee_id', 'device_id', 'organization_ai_tool_id', 'finding_type', 'severity', 'status']; }
}
