<?php

namespace App\Application\Devices\Services;

use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Registers devices idempotently and records their initial employee assignment. */
final readonly class DeviceRegistrationService
{
    public function __construct(private DeviceRepositoryInterface $devices) {}

    public function execute(string $organizationId, array $attributes): Model
    {
        $attributes = $this->normalize($attributes);

        if (! empty($attributes['device_uuid']) && $this->devices->findByDeviceUuid($organizationId, $attributes['device_uuid'])) {
            throw ValidationException::withMessages(['device_uuid' => ['The device is already registered.']]);
        }

        return DB::transaction(function () use ($organizationId, $attributes): Model {
            $employeeId = $attributes['current_employee_id'] ?? null;
            $device = $this->devices->create($attributes + [
                'organization_id' => $organizationId,
                'registration_status' => 'pending',
                'device_type' => 'workstation',
                'ownership_type' => 'corporate',
                'registered_at' => now(),
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

    public function get(string $organizationId, string $deviceId): Model
    {
        return $this->devices->findForOrganization($organizationId, $deviceId)
            ?? throw (new ModelNotFoundException)->setModel('Device', [$deviceId]);
    }

    public function list(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->devices->paginateForOrganization($organizationId, $filters, $perPage);
    }

    public function update(string $organizationId, string $deviceId, array $attributes): Model
    {
        $attributes = $this->normalize($attributes);

        return DB::transaction(function () use ($organizationId, $deviceId, $attributes): Model {
            $device = $this->get($organizationId, $deviceId);
            $previousEmployeeId = $device->getAttribute('current_employee_id');
            $newEmployeeId = $attributes['current_employee_id'] ?? $previousEmployeeId;

            if (array_key_exists('current_employee_id', $attributes) && $newEmployeeId !== $previousEmployeeId) {
                $this->devices->closeActiveAssignments($organizationId, $deviceId, now());

                if ($newEmployeeId) {
                    $this->devices->createAssignment([
                        'organization_id' => $organizationId,
                        'device_id' => $deviceId,
                        'employee_id' => $newEmployeeId,
                        'assigned_at' => now(),
                        'assignment_type' => 'primary',
                    ]);
                }
            }

            return $this->devices->update($device, $attributes);
        });
    }

    public function heartbeat(string $organizationId, string $deviceId, array $attributes): Model
    {
        $device = $this->get($organizationId, $deviceId);
        $updates = [
            'last_seen_at' => $attributes['last_seen_at'] ?? now(),
            'registration_status' => $attributes['status'] ?? $device->getAttribute('registration_status'),
        ];
        if (($attributes['status'] ?? null) === 'revoked') $updates['revoked_at'] = now();
        if (($attributes['status'] ?? null) === 'retired') $updates['retired_at'] = now();

        return $this->devices->update($device, $updates);
    }

    public function delete(string $organizationId, string $deviceId): void
    {
        DB::transaction(function () use ($organizationId, $deviceId): void {
            $device = $this->get($organizationId, $deviceId);
            $this->devices->closeActiveAssignments($organizationId, $deviceId, now());
            $this->devices->delete($device);
        });
    }

    public function verify(string $organizationId, string $deviceId): Model
    {
        $device = $this->get($organizationId, $deviceId);

        return $this->devices->update($device, ['registration_status' => 'verified', 'verified_at' => now()]);
    }

    private function normalize(array $attributes): array
    {
        $map = [
            'device_name' => 'hostname',
            'device_id' => 'device_uuid',
            'employee_id' => 'current_employee_id',
            'status' => 'registration_status',
        ];

        foreach ($map as $input => $column) {
            if (array_key_exists($input, $attributes)) {
                $attributes[$column] = $attributes[$input];
                unset($attributes[$input]);
            }
        }

        if (($attributes['registration_status'] ?? null) === 'verified') $attributes['verified_at'] ??= now();
        if (($attributes['registration_status'] ?? null) === 'revoked') $attributes['revoked_at'] ??= now();
        if (($attributes['registration_status'] ?? null) === 'retired') $attributes['retired_at'] ??= now();

        return $attributes;
    }
}
