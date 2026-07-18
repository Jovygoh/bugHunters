<?php

namespace App\Domain\Departments\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends RepositoryInterface<Department> */
interface DepartmentRepositoryInterface extends RepositoryInterface
{
    public function findByCode(string $organizationId, string $code): ?Department;

    public function hierarchy(string $organizationId): Collection;

    public function findForOrganization(string $organizationId, string $departmentId): ?Department;

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function hasEmployees(string $organizationId, string $departmentId): bool;

    public function hasChildren(string $organizationId, string $departmentId): bool;

    public function isDescendant(string $organizationId, string $ancestorId, string $descendantId): bool;

    public function rebuildClosure(string $organizationId): void;
}
