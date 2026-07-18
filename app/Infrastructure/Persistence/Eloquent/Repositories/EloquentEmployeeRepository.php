<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Employees\Repositories\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class EloquentEmployeeRepository extends AbstractEloquentRepository implements EmployeeRepositoryInterface
{
    public function __construct(Employee $model) { parent::__construct($model); }

    public function findByEmployeeNumber(string $organizationId, string $employeeNumber): ?Employee
    {
        return Employee::query()->where('organization_id', $organizationId)->where('employee_number', $employeeNumber)->first();
    }

    public function paginateByDepartment(string $organizationId, string $departmentId, int $perPage = 25): LengthAwarePaginator
    {
        return Employee::query()->where('organization_id', $organizationId)->where('department_id', $departmentId)->paginate($perPage);
    }

    public function findForOrganization(string $organizationId, string $employeeId): ?Employee
    {
        return Employee::query()
            ->with(['user.roles', 'department', 'managerEmployee'])
            ->where('organization_id', $organizationId)
            ->whereKey($employeeId)
            ->first();
    }

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Employee::query()
            ->with(['user', 'department', 'managerEmployee'])
            ->where('organization_id', $organizationId);

        foreach (array_intersect_key($filters, array_flip(['department_id', 'manager_employee_id', 'status', 'risk_level', 'employment_type'])) as $column => $value) {
            $query->where($column, $value);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search): void {
                $query->where('display_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('normalized_work_email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('display_name')->paginate($perPage);
    }

    public function managerUsersForEmployee(string $organizationId, string $employeeId): Collection
    {
        $employee = Employee::query()
            ->with(['managerEmployee.user', 'department.managerEmployee.user'])
            ->where('organization_id', $organizationId)
            ->whereKey($employeeId)
            ->first();

        if (! $employee) {
            return (new User)->newCollection();
        }

        return (new User)->newCollection([
            $employee->managerEmployee?->user,
            $employee->department?->managerEmployee?->user,
        ])->filter()->unique('id')->values();
    }

    protected function filterable(): array { return ['organization_id', 'department_id', 'manager_employee_id', 'status', 'risk_level']; }
}
