<?php

namespace App\Http\Requests\Api\V1\Employees;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEmployeeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('work_email')) {
            $this->merge(['normalized_work_email' => mb_strtolower(trim((string) $this->input('work_email')))]);
        }
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('employees:manage');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'user_id' => [
                'nullable', 'uuid',
                Rule::exists('users', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at'),
                Rule::unique('employees', 'user_id')->whereNull('deleted_at'),
            ],
            'department_id' => ['nullable', 'uuid', Rule::exists('departments', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'manager_employee_id' => ['nullable', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'employee_number' => ['nullable', 'string', 'max:100', Rule::unique('employees')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'display_name' => ['sometimes', 'string', 'max:200'],
            'work_email' => ['nullable', 'email:rfc', 'max:320'],
            'normalized_work_email' => [
                'nullable',
                Rule::unique('employees')->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'job_title' => ['nullable', 'string', 'max:150'],
            'employment_type' => ['nullable', 'string', 'max:30'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'suspended'])],
            'risk_level' => ['sometimes', Rule::in(['low', 'normal', 'medium', 'high', 'critical'])],
            'hired_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
