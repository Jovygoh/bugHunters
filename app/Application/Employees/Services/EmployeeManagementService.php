<?php

namespace App\Application\Employees\Services;

use App\Domain\Employees\Repositories\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** Creates and updates tenant-scoped employee records and reporting assignments. */
final readonly class EmployeeManagementService
{
    public function __construct(private EmployeeRepositoryInterface $employees) {}

    public function create(string $organizationId, array $attributes): Model
    {
        if (! empty($attributes['employee_number']) && $this->employees->findByEmployeeNumber($organizationId, $attributes['employee_number'])) {
            throw ValidationException::withMessages(['employee_number' => ['The employee number is already in use.']]);
        }

        $attributes['organization_id'] = $organizationId;
        $attributes['status'] ??= 'active';

        return $this->employees->create($attributes);
    }

    public function update(string $organizationId, string $employeeId, array $attributes): Model
    {
        $employee = $this->employees->findOrFail($employeeId);
        $this->assertTenant($employee, $organizationId);

        return $this->employees->update($employee, $attributes);
    }

    private function assertTenant(Model $model, string $organizationId): void
    {
        abort_unless($model->getAttribute('organization_id') === $organizationId, 404);
    }
}

