<?php

namespace App\Http\Requests\Api\V1\Employees;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEmployeeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('work_email')) {
            $email = $this->input('work_email');
            $this->merge(['normalized_work_email' => $email ? mb_strtolower(trim((string) $email)) : null]);
        }
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('employees:manage');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;
        $employeeId = $this->route('employee');

        return [
            'user_id' => [
                'sometimes', 'nullable', 'uuid',
                Rule::exists('users', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at'),
                Rule::unique('employees', 'user_id')->ignore($employeeId)->whereNull('deleted_at'),
            ],
            'department_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('departments', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'manager_employee_id' => [
                'sometimes', 'nullable', 'uuid', Rule::notIn([$employeeId]),
                Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'employee_number' => [
                'sometimes', 'nullable', 'string', 'max:100',
                Rule::unique('employees')->ignore($employeeId)->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'display_name' => ['sometimes', 'string', 'max:200'],
            'work_email' => ['sometimes', 'nullable', 'email:rfc', 'max:320'],
            'normalized_work_email' => [
                'sometimes', 'nullable',
                Rule::unique('employees')->ignore($employeeId)->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:150'],
            'employment_type' => ['sometimes', 'nullable', 'string', 'max:30'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'suspended', 'terminated'])],
            'risk_level' => ['sometimes', Rule::in(['low', 'normal', 'medium', 'high', 'critical'])],
            'hired_at' => ['sometimes', 'nullable', 'date'],
            'terminated_at' => ['sometimes', 'nullable', 'date'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
