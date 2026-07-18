<?php

namespace App\Domain\Departments\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

/** @extends RepositoryInterface<Department> */
interface DepartmentRepositoryInterface extends RepositoryInterface
{
    public function findByCode(string $organizationId, string $code): ?Department;

    public function hierarchy(string $organizationId): Collection;
}

