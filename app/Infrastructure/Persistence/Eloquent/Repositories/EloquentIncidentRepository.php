<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Incidents\Repositories\IncidentRepositoryInterface;
use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\IncidentEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Organization;

final class EloquentIncidentRepository extends AbstractEloquentRepository implements IncidentRepositoryInterface
{
    public function __construct(Incident $model) { parent::__construct($model); }

    public function findByNumber(string $organizationId, int $incidentNumber): ?Incident
    {
        return Incident::query()->where('organization_id', $organizationId)->where('incident_number', $incidentNumber)->first();
    }

    public function openForAssignee(string $organizationId, string $userId): Collection
    {
        return Incident::query()->where('organization_id', $organizationId)->where('assigned_to', $userId)->whereNotIn('status', ['resolved', 'closed'])->orderByDesc('created_at')->get();
    }

    public function createEvent(array $attributes): IncidentEvent { return IncidentEvent::query()->create($attributes); }

    public function createComment(array $attributes): IncidentComment { return IncidentComment::query()->create($attributes); }

    public function findForOrganization(string $organizationId, string $incidentId): ?Incident
    {
        return Incident::query()
            ->with(['employee', 'device', 'organizationAiTool', 'policy', 'assignee', 'events'])
            ->where('organization_id', $organizationId)
            ->whereKey($incidentId)
            ->first();
    }

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Incident::query()
            ->with(['employee', 'device', 'organizationAiTool', 'policy', 'assignee'])
            ->where('organization_id', $organizationId);

        foreach (array_intersect_key($filters, array_flip([
            'employee_id', 'device_id', 'organization_ai_tool_id', 'policy_id',
            'severity', 'status', 'priority', 'assigned_to', 'action',
        ])) as $column => $value) {
            $query->where($column, $value);
        }

        if (isset($filters['detected_from'])) $query->where('detected_at', '>=', $filters['detected_from']);
        if (isset($filters['detected_to'])) $query->where('detected_at', '<=', $filters['detected_to']);

        return $query->orderByDesc('detected_at')->paginate($perPage);
    }

    public function nextIncidentNumber(string $organizationId): int
    {
        Organization::query()->whereKey($organizationId)->lockForUpdate()->firstOrFail();

        return ((int) Incident::query()
            ->where('organization_id', $organizationId)
            ->withTrashed()
            ->max('incident_number')) + 1;
    }

    protected function filterable(): array { return ['organization_id', 'incident_type', 'severity', 'status', 'priority', 'employee_id', 'device_id', 'organization_ai_tool_id', 'assigned_to']; }
}
