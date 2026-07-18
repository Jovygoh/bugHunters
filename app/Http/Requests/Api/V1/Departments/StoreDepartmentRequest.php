<?php

namespace App\Http\Requests\Api\V1\Departments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('departments:manage');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'parent_department_id' => ['nullable', 'uuid', Rule::exists('departments', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'manager_employee_id' => ['nullable', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:100', Rule::unique('departments')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

