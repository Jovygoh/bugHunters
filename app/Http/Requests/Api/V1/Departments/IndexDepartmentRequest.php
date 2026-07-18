<?php

namespace App\Http\Requests\Api\V1\Departments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('departments:view');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'search' => ['sometimes', 'string', 'max:200'],
            'parent_department_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('departments', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'manager_employee_id' => ['sometimes', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'archived'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->safe()->only(['search', 'parent_department_id', 'manager_employee_id', 'status']);
    }
}

