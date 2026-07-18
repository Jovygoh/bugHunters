<?php

namespace App\Application\Devices\Services;

use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Registers devices idempotently and records their initial employee assignment. */
final readonly class DeviceRegistrationService
{
    public function __construct(private DeviceRepositoryInterface $devices) {}

    public function execute(string $organizationId, array $attributes): Model
    {
        if (! empty($attributes['device_uuid']) && $this->devices->findByDeviceUuid($organizationId, $attributes['device_uuid'])) {
            throw ValidationException::withMessages(['device_uuid' => ['The device is already registered.']]);
        }

        return DB::transaction(function () use ($organizationId, $attributes): Model {
            $employeeId = $attributes['current_employee_id'] ?? null;
            $device = $this->devices->create($attributes + [
                'organization_id' => $organizationId,
                'registration_status' => 'pending',
            ]);

            if ($employeeId) {
                $this->devices->createAssignment([
                    'organization_id' => $organizationId,
                    'device_id' => $device->getKey(),
                    'employee_id' => $employeeId,
                    'assigned_at' => now(),
                    'assignment_type' => 'primary',
                ]);
            }

            return $device;
        });
    }

    public function verify(string $organizationId, string $deviceId): Model
    {
        $device = $this->devices->findOrFail($deviceId);
        abort_unless($device->getAttribute('organization_id') === $organizationId, 404);

        return $this->devices->update($device, ['registration_status' => 'verified', 'verified_at' => now()]);
    }
}

