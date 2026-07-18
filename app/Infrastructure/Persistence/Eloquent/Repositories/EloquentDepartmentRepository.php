<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Departments\Repositories\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

final class EloquentDepartmentRepository extends AbstractEloquentRepository implements DepartmentRepositoryInterface
{
    public function __construct(Department $model) { parent::__construct($model); }

    public function findByCode(string $organizationId, string $code): ?Department
    {
        return Department::query()->where('organization_id', $organizationId)->where('code', $code)->first();
    }

    public function hierarchy(string $organizationId): Collection
    {
        return Department::query()->where('organization_id', $organizationId)->with(['children', 'manager'])->orderBy('name')->get();
    }

    protected function filterable(): array { return ['organization_id', 'parent_department_id', 'manager_employee_id', 'status']; }
}

