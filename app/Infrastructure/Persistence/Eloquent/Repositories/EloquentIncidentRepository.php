<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Incidents\Repositories\IncidentRepositoryInterface;
use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\IncidentEvent;
use App\Models\IncidentEvidence;
use Illuminate\Database\Eloquent\Collection;

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

    public function createEvidence(array $attributes): IncidentEvidence { return IncidentEvidence::query()->create($attributes); }

    protected function filterable(): array { return ['organization_id', 'incident_type', 'severity', 'status', 'priority', 'employee_id', 'device_id', 'organization_ai_tool_id', 'assigned_to']; }
}

