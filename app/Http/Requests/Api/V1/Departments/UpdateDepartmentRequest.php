<?php

namespace App\Http\Requests\Api\V1\Departments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('departments:manage');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;
        $departmentId = $this->route('department');

        return [
            'parent_department_id' => [
                'sometimes', 'nullable', 'uuid', Rule::notIn([$departmentId]),
                Rule::exists('departments', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'manager_employee_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'name' => ['sometimes', 'string', 'max:200'],
            'code' => [
                'sometimes', 'string', 'max:100',
                Rule::unique('departments')->ignore($departmentId)->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}

