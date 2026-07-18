<?php

namespace App\Domain\Devices\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\DevicePostureSnapshot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends RepositoryInterface<Device> */
interface DeviceRepositoryInterface extends RepositoryInterface
{
    public function findByDeviceUuid(string $organizationId, string $deviceUuid): ?Device;

    public function activeForEmployee(string $organizationId, string $employeeId): Collection;

    public function createAssignment(array $attributes): DeviceAssignment;

    public function createPostureSnapshot(array $attributes): DevicePostureSnapshot;

    public function findForOrganization(string $organizationId, string $deviceId): ?Device;

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function closeActiveAssignments(string $organizationId, string $deviceId, mixed $unassignedAt): int;
}
