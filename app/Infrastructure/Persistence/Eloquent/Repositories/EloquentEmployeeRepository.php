<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Employees\Repositories\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    protected function filterable(): array { return ['organization_id', 'department_id', 'manager_employee_id', 'status', 'risk_level']; }
}

