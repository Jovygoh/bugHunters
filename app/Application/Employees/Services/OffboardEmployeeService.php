<?php

namespace App\Application\Employees\Services;

use App\Domain\Authentication\Repositories\UserRepositoryInterface;
use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use App\Domain\Employees\Repositories\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Atomically terminates an employee, disables linked access, and retires assigned devices. */
final readonly class OffboardEmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employees,
        private UserRepositoryInterface $users,
        private DeviceRepositoryInterface $devices,
    ) {}

    public function execute(string $organizationId, string $employeeId, ?string $terminatedAt = null): Model
    {
        return DB::transaction(function () use ($organizationId, $employeeId, $terminatedAt): Model {
            $employee = $this->employees->findOrFail($employeeId);
            abort_unless($employee->getAttribute('organization_id') === $organizationId, 404);

            if ($userId = $employee->getAttribute('user_id')) {
                $user = $this->users->findOrFail($userId);
                $this->users->update($user, ['status' => 'disabled']);
            }

            foreach ($this->devices->activeForEmployee($organizationId, $employeeId) as $device) {
                $this->devices->update($device, ['current_employee_id' => null, 'retired_at' => now()]);
            }

            return $this->employees->update($employee, [
                'status' => 'terminated',
                'terminated_at' => $terminatedAt ?? now(),
            ]);
        });
    }
}

