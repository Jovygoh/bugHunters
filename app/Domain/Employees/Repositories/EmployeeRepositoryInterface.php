<?php

namespace App\Domain\Employees\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/** @extends RepositoryInterface<Employee> */
interface EmployeeRepositoryInterface extends RepositoryInterface
{
    public function findByEmployeeNumber(string $organizationId, string $employeeNumber): ?Employee;

    public function paginateByDepartment(string $organizationId, string $departmentId, int $perPage = 25): LengthAwarePaginator;

    public function findForOrganization(string $organizationId, string $employeeId): ?Employee;

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function managerUsersForEmployee(string $organizationId, string $employeeId): Collection;
}
