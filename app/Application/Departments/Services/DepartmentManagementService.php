<?php

namespace App\Application\Departments\Services;

use App\Domain\Departments\Repositories\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
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

        return $this->departments->create($attributes);
    }

    public function update(string $organizationId, string $departmentId, array $attributes): Model
    {
        $department = $this->departments->findOrFail($departmentId);
        abort_unless($department->getAttribute('organization_id') === $organizationId, 404);

        if (($attributes['parent_department_id'] ?? null) === $departmentId) {
            throw ValidationException::withMessages(['parent_department_id' => ['A department cannot be its own parent.']]);
        }

        return $this->departments->update($department, $attributes);
    }
}

