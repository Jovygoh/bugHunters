<?php

namespace App\Application\Devices\Services;

use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use App\Models\DevicePostureSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Validates posture results, stores immutable snapshots, and updates the device read state. */
final readonly class DevicePostureService
{
    public function __construct(private DeviceRepositoryInterface $devices) {}

    public function record(string $organizationId, string $deviceId, array $posture): DevicePostureSnapshot
    {
        if (! in_array($posture['compliance_status'], ['compliant', 'noncompliant', 'unknown'], true)) {
            throw ValidationException::withMessages(['compliance_status' => ['Invalid compliance status.']]);
        }

        return DB::transaction(function () use ($organizationId, $deviceId, $posture): DevicePostureSnapshot {
            $device = $this->devices->findOrFail($deviceId);
            abort_unless($device->getAttribute('organization_id') === $organizationId, 404);

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
    }
}

