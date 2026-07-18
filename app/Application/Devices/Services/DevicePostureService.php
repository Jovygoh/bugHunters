<?php

namespace App\Application\Devices\Services;

use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use App\Models\DevicePostureSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Application\Notifications\Services\ManagerNotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/** Validates posture results, stores immutable snapshots, and updates the device read state. */
final readonly class DevicePostureService
{
    public function __construct(
        private DeviceRepositoryInterface $devices,
        private ManagerNotificationService $managerNotifications,
    ) {}

    public function record(string $organizationId, string $deviceId, array $posture): DevicePostureSnapshot
    {
        if (! in_array($posture['compliance_status'], ['compliant', 'noncompliant', 'unknown'], true)) {
            throw ValidationException::withMessages(['compliance_status' => ['Invalid compliance status.']]);
        }

        $device = $this->devices->findForOrganization($organizationId, $deviceId)
            ?? throw (new ModelNotFoundException)->setModel('Device', [$deviceId]);

        $snapshot = DB::transaction(function () use ($organizationId, $deviceId, $posture, $device): DevicePostureSnapshot {

            $snapshot = $this->devices->createPostureSnapshot($posture + [
                'organization_id' => $organizationId,
                'device_id' => $deviceId,
                'observed_at' => $posture['observed_at'] ?? now(),
            ]);

            $this->devices->update($device, [
                'compliance_status' => $posture['compliance_status'],
                'last_seen_at' => $posture['observed_at'] ?? now(),
            ]);

            return $snapshot;
        });

        $riskScore = isset($posture['risk_score']) ? (float) $posture['risk_score'] : null;
        if (
            $device->getAttribute('current_employee_id')
            && ($posture['compliance_status'] === 'noncompliant' || ($riskScore ?? 0) >= 70)
        ) {
            $this->managerNotifications->deviceSuspicious(
                $organizationId,
                $device->getAttribute('current_employee_id'),
                $deviceId,
                $posture['compliance_status'],
                $riskScore,
            );
        }

        return $snapshot;
    }
}
