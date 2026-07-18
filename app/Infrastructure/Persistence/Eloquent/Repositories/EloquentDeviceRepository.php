<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\DevicePostureSnapshot;
use Illuminate\Database\Eloquent\Collection;

final class EloquentDeviceRepository extends AbstractEloquentRepository implements DeviceRepositoryInterface
{
    public function __construct(Device $model) { parent::__construct($model); }

    public function findByDeviceUuid(string $organizationId, string $deviceUuid): ?Device
    {
        return Device::query()->where('organization_id', $organizationId)->where('device_uuid', $deviceUuid)->first();
    }

    public function activeForEmployee(string $organizationId, string $employeeId): Collection
    {
        return Device::query()->where('organization_id', $organizationId)->where('current_employee_id', $employeeId)->whereNull('retired_at')->get();
    }

    public function createAssignment(array $attributes): DeviceAssignment { return DeviceAssignment::query()->create($attributes); }

    public function createPostureSnapshot(array $attributes): DevicePostureSnapshot { return DevicePostureSnapshot::query()->create($attributes); }

    protected function filterable(): array { return ['organization_id', 'current_employee_id', 'department_id', 'registration_status', 'compliance_status', 'trust_level']; }
}

