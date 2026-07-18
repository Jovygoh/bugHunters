<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Departments\Repositories\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use App\Models\DepartmentClosure;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

final class EloquentDepartmentRepository extends AbstractEloquentRepository implements DepartmentRepositoryInterface
{
    public function __construct(Department $model) { parent::__construct($model); }

    public function findByCode(string $organizationId, string $code): ?Department
    {
        return Department::query()->where('organization_id', $organizationId)->where('code', $code)->first();
    }

    public function hierarchy(string $organizationId): Collection
    {
        return Department::query()->where('organization_id', $organizationId)->with(['children', 'managerEmployee'])->orderBy('name')->get();
    }

    public function findForOrganization(string $organizationId, string $departmentId): ?Department
    {
        return Department::query()
            ->with(['parentDepartment', 'managerEmployee'])
            ->withCount(['employees', 'children'])
            ->where('organization_id', $organizationId)
            ->whereKey($departmentId)
            ->first();
    }

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Department::query()
            ->with(['parentDepartment', 'managerEmployee'])
            ->withCount(['employees', 'children'])
            ->where('organization_id', $organizationId);

        foreach (array_intersect_key($filters, array_flip(['parent_department_id', 'manager_employee_id', 'status'])) as $column => $value) {
            $query->where($column, $value);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function hasEmployees(string $organizationId, string $departmentId): bool
    {
        return Employee::query()->where('organization_id', $organizationId)->where('department_id', $departmentId)->exists();
    }

    public function hasChildren(string $organizationId, string $departmentId): bool
    {
        return Department::query()->where('organization_id', $organizationId)->where('parent_department_id', $departmentId)->exists();
    }

    public function isDescendant(string $organizationId, string $ancestorId, string $descendantId): bool
    {
        return DepartmentClosure::query()
            ->where('organization_id', $organizationId)
            ->where('ancestor_department_id', $ancestorId)
            ->where('descendant_department_id', $descendantId)
            ->where('depth', '>', 0)
            ->exists();
    }

    public function rebuildClosure(string $organizationId): void
    {
        $departments = Department::query()
            ->where('organization_id', $organizationId)
            ->get(['id', 'parent_department_id'])
            ->keyBy('id');

        $rows = [];
        foreach ($departments as $department) {
            $rows[] = [
                'id' => (string) Str::uuid7(),
                'organization_id' => $organizationId,
                'ancestor_department_id' => $department->id,
                'descendant_department_id' => $department->id,
                'depth' => 0,
                'created_at' => now(),
            ];

            $ancestorId = $department->parent_department_id;
            $depth = 1;
            $visited = [$department->id => true];

            while ($ancestorId && isset($departments[$ancestorId]) && ! isset($visited[$ancestorId])) {
                $visited[$ancestorId] = true;
                $rows[] = [
                    'id' => (string) Str::uuid7(),
                    'organization_id' => $organizationId,
                    'ancestor_department_id' => $ancestorId,
                    'descendant_department_id' => $department->id,
                    'depth' => $depth++,
                    'created_at' => now(),
                ];
                $ancestorId = $departments[$ancestorId]->parent_department_id;
            }
        }

        DepartmentClosure::query()->where('organization_id', $organizationId)->delete();
        if ($rows !== []) {
            DepartmentClosure::query()->insert($rows);
        }
    }

    protected function filterable(): array { return ['organization_id', 'parent_department_id', 'manager_employee_id', 'status']; }
}
