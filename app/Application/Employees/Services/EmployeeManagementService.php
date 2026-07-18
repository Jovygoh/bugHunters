<?php

namespace App\Application\Employees\Services;

use App\Domain\Employees\Repositories\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $attributes['display_name'] ??= trim($attributes['first_name'].' '.$attributes['last_name']);
        $attributes['normalized_work_email'] = isset($attributes['work_email'])
            ? mb_strtolower(trim($attributes['work_email']))
            : null;
        $attributes['status'] ??= 'active';

        return $this->employees->create($attributes);
    }

    public function update(string $organizationId, string $employeeId, array $attributes): Model
    {
        $employee = $this->get($organizationId, $employeeId);

        if (isset($attributes['first_name']) || isset($attributes['last_name'])) {
            $attributes['display_name'] ??= trim(
                ($attributes['first_name'] ?? $employee->getAttribute('first_name')).' '.
                ($attributes['last_name'] ?? $employee->getAttribute('last_name'))
            );
        }

        if (array_key_exists('work_email', $attributes)) {
            $attributes['normalized_work_email'] = $attributes['work_email']
                ? mb_strtolower(trim($attributes['work_email']))
                : null;
        }

        return $this->employees->update($employee, $attributes);
    }

    public function get(string $organizationId, string $employeeId): Model
    {
        return $this->employees->findForOrganization($organizationId, $employeeId)
            ?? throw (new ModelNotFoundException)->setModel('Employee', [$employeeId]);
    }

    public function list(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->employees->paginateForOrganization($organizationId, $filters, $perPage);
    }

    public function delete(string $organizationId, string $employeeId): void
    {
        $this->employees->delete($this->get($organizationId, $employeeId));
    }

}
