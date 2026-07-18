<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\DevicePostureSnapshot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function findForOrganization(string $organizationId, string $deviceId): ?Device
    {
        return Device::query()
            ->with(['currentEmployee', 'department', 'assignments.employee'])
            ->where('organization_id', $organizationId)
            ->whereKey($deviceId)
            ->first();
    }

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Device::query()
            ->with(['currentEmployee', 'department'])
            ->where('organization_id', $organizationId);

        foreach (array_intersect_key($filters, array_flip([
            'current_employee_id', 'department_id', 'device_type', 'ownership_type',
            'registration_status', 'compliance_status', 'trust_level', 'operating_system',
        ])) as $column => $value) {
            $query->where($column, $value);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($query) => $query
                ->where('hostname', 'like', "%{$search}%")
                ->orWhere('device_uuid', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhere('asset_tag', 'like', "%{$search}%"));
        }

        return $query->orderByDesc('last_seen_at')->paginate($perPage);
    }

    public function closeActiveAssignments(string $organizationId, string $deviceId, mixed $unassignedAt): int
    {
        return DeviceAssignment::query()
            ->where('organization_id', $organizationId)
            ->where('device_id', $deviceId)
            ->whereNull('unassigned_at')
            ->update(['unassigned_at' => $unassignedAt, 'updated_at' => now()]);
    }

    protected function filterable(): array { return ['organization_id', 'current_employee_id', 'department_id', 'registration_status', 'compliance_status', 'trust_level']; }
}
