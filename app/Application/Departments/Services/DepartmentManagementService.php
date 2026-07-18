<?php

namespace App\Application\Departments\Services;

use App\Domain\Departments\Repositories\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Maintains department records while preventing duplicate codes and self-parenting. */
final readonly class DepartmentManagementService
{
    public function __construct(private DepartmentRepositoryInterface $departments) {}

    public function create(string $organizationId, array $attributes): Model
    {
        if ($this->departments->findByCode($organizationId, $attributes['code'])) {
            throw ValidationException::withMessages(['code' => ['The department code is already in use.']]);
        }

        $attributes['organization_id'] = $organizationId;

        return DB::transaction(function () use ($organizationId, $attributes): Model {
            $department = $this->departments->create($attributes);
            $this->departments->rebuildClosure($organizationId);

            return $department;
        });
    }

    public function update(string $organizationId, string $departmentId, array $attributes): Model
    {
        $department = $this->get($organizationId, $departmentId);

        if (($attributes['parent_department_id'] ?? null) === $departmentId) {
            throw ValidationException::withMessages(['parent_department_id' => ['A department cannot be its own parent.']]);
        }

        if (
            ! empty($attributes['parent_department_id'])
            && $this->departments->isDescendant($organizationId, $departmentId, $attributes['parent_department_id'])
        ) {
            throw ValidationException::withMessages(['parent_department_id' => ['A department cannot be moved below one of its descendants.']]);
        }

        return DB::transaction(function () use ($organizationId, $department, $attributes): Model {
            $updated = $this->departments->update($department, $attributes);
            $this->departments->rebuildClosure($organizationId);

            return $updated;
        });
    }

    public function get(string $organizationId, string $departmentId): Model
    {
        return $this->departments->findForOrganization($organizationId, $departmentId)
            ?? throw (new ModelNotFoundException)->setModel('Department', [$departmentId]);
    }

    public function list(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->departments->paginateForOrganization($organizationId, $filters, $perPage);
    }

    public function delete(string $organizationId, string $departmentId): void
    {
        $department = $this->get($organizationId, $departmentId);

        if ($this->departments->hasEmployees($organizationId, $departmentId)) {
            throw ValidationException::withMessages(['department' => ['Move all employees before deleting this department.']]);
        }

        if ($this->departments->hasChildren($organizationId, $departmentId)) {
            throw ValidationException::withMessages(['department' => ['Move or delete all child departments first.']]);
        }

        DB::transaction(function () use ($organizationId, $department): void {
            $this->departments->delete($department);
            $this->departments->rebuildClosure($organizationId);
        });
    }
}
