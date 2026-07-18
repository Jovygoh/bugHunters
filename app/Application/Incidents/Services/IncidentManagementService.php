<?php

namespace App\Application\Incidents\Services;

use App\Domain\Incidents\Repositories\IncidentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Creates incidents and enforces valid, auditable lifecycle transitions. */
final readonly class IncidentManagementService
{
    private const TRANSITIONS = [
        'open' => ['triaged', 'closed'],
        'triaged' => ['investigating', 'closed'],
        'investigating' => ['contained', 'resolved'],
        'contained' => ['resolved'],
        'resolved' => ['closed', 'investigating'],
        'closed' => [],
    ];

    public function __construct(private IncidentRepositoryInterface $incidents) {}

    public function create(string $organizationId, array $attributes): Model
    {
        return $this->incidents->create($attributes + [
            'organization_id' => $organizationId,
            'status' => 'open',
            'detected_at' => $attributes['detected_at'] ?? now(),
        ]);
    }

    public function transition(string $organizationId, string $incidentId, string $status, ?string $actorId = null): Model
    {
        return DB::transaction(function () use ($organizationId, $incidentId, $status, $actorId): Model {
            $incident = $this->incidents->findOrFail($incidentId);
            abort_unless($incident->getAttribute('organization_id') === $organizationId, 404);
            $current = $incident->getAttribute('status');

            if (! in_array($status, self::TRANSITIONS[$current] ?? [], true)) {
                throw ValidationException::withMessages(['status' => ["Cannot transition an incident from {$current} to {$status}."]]);
            }

            $attributes = ['status' => $status, 'lock_version' => $incident->getAttribute('lock_version') + 1];
            if ($status === 'resolved') $attributes['resolved_at'] = now();
            if ($status === 'closed') $attributes['closed_at'] = now();

            $updated = $this->incidents->update($incident, $attributes);
            $this->incidents->createEvent([
                'organization_id' => $organizationId,
                'incident_id' => $incidentId,
                'actor_user_id' => $actorId,
                'event_type' => 'status_changed',
                'from_status' => $current,
                'to_status' => $status,
                'occurred_at' => now(),
            ]);

            return $updated;
        });
    }
}

