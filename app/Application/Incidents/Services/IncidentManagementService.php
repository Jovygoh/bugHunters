<?php

namespace App\Application\Incidents\Services;

use App\Domain\Incidents\Repositories\IncidentRepositoryInterface;
use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use App\Application\Notifications\Services\ManagerNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    public function __construct(
        private IncidentRepositoryInterface $incidents,
        private DeviceRepositoryInterface $devices,
        private ManagerNotificationService $managerNotifications,
    ) {}

    public function create(string $organizationId, array $attributes): Model
    {
        $incident = DB::transaction(function () use ($organizationId, $attributes): Model {
            $riskScore = (float) $attributes['risk'];
            $incident = $this->incidents->create([
                'organization_id' => $organizationId,
                'incident_number' => $this->incidents->nextIncidentNumber($organizationId),
                'title' => $attributes['title'] ?? 'AI governance policy incident',
                'description' => $attributes['description'] ?? null,
                'incident_type' => 'policy_violation',
                'severity' => $this->severity($riskScore),
                'priority' => $this->priority($riskScore),
                'status' => 'open',
                'employee_id' => $attributes['employee_id'],
                'device_id' => $attributes['device_id'],
                'organization_ai_tool_id' => $attributes['ai_tool_id'],
                'policy_id' => $attributes['policy_id'],
                'risk_score' => $riskScore,
                'action' => $attributes['action'],
                'metadata' => $attributes['metadata'] ?? null,
                'source' => 'api',
                'reported_by' => $attributes['actor_user_id'],
                'detected_at' => $attributes['timestamp'],
            ]);

            $this->incidents->createEvent([
                'organization_id' => $organizationId,
                'incident_id' => $incident->getKey(),
                'actor_user_id' => $attributes['actor_user_id'],
                'event_type' => 'created',
                'to_status' => 'open',
                'payload' => ['risk_score' => $riskScore, 'action' => $attributes['action']],
                'occurred_at' => now(),
            ]);

            return $incident;
        });

        if ((float) $attributes['risk'] >= 70) {
            $this->managerNotifications->highRiskIncident(
                $organizationId,
                $attributes['employee_id'],
                $incident->getKey(),
                (float) $attributes['risk'],
            );
        }

        return $incident;
    }

    public function get(string $organizationId, string $incidentId): Model
    {
        return $this->incidents->findForOrganization($organizationId, $incidentId)
            ?? throw (new ModelNotFoundException)->setModel('Incident', [$incidentId]);
    }

    public function list(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->incidents->paginateForOrganization($organizationId, $filters, $perPage);
    }

    public function update(string $organizationId, string $incidentId, array $attributes): Model
    {
        $incident = $this->get($organizationId, $incidentId);
        $updates = $attributes;
        $employeeId = $updates['employee_id'] ?? $incident->getAttribute('employee_id');
        $deviceId = $updates['device_id'] ?? $incident->getAttribute('device_id');
        $device = $this->devices->findForOrganization($organizationId, $deviceId);

        if (! $device || $device->getAttribute('current_employee_id') !== $employeeId) {
            throw ValidationException::withMessages(['device_id' => ['The device is not assigned to the selected employee.']]);
        }

        if (array_key_exists('risk', $updates)) {
            $riskScore = (float) $updates['risk'];
            $updates['risk_score'] = $riskScore;
            $updates['severity'] = $this->severity($riskScore);
            $updates['priority'] = $this->priority($riskScore);
            unset($updates['risk']);
        }
        if (array_key_exists('ai_tool_id', $updates)) {
            $updates['organization_ai_tool_id'] = $updates['ai_tool_id'];
            unset($updates['ai_tool_id']);
        }
        if (array_key_exists('timestamp', $updates)) {
            $updates['detected_at'] = $updates['timestamp'];
            unset($updates['timestamp']);
        }

        return $this->incidents->update($incident, $updates);
    }

    public function transition(string $organizationId, string $incidentId, string $status, ?string $actorId = null): Model
    {
        return DB::transaction(function () use ($organizationId, $incidentId, $status, $actorId): Model {
            $incident = $this->get($organizationId, $incidentId);
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

    private function severity(float $riskScore): string
    {
        return match (true) {
            $riskScore >= 90 => 'critical',
            $riskScore >= 70 => 'high',
            $riskScore >= 40 => 'medium',
            default => 'low',
        };
    }

    private function priority(float $riskScore): string
    {
        return match (true) {
            $riskScore >= 90 => 'urgent',
            $riskScore >= 70 => 'high',
            $riskScore >= 40 => 'normal',
            default => 'low',
        };
    }
}
