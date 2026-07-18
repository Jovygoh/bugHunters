<?php

namespace App\Http\Requests\Api\V1\Employees;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('employees:view');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'search' => ['sometimes', 'string', 'max:200'],
            'department_id' => ['sometimes', 'uuid', Rule::exists('departments', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'manager_employee_id' => ['sometimes', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'suspended', 'terminated'])],
            'risk_level' => ['sometimes', Rule::in(['low', 'normal', 'medium', 'high', 'critical'])],
            'employment_type' => ['sometimes', 'string', 'max:30'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->safe()->only([
            'search', 'department_id', 'manager_employee_id', 'status', 'risk_level', 'employment_type',
        ]);
    }
}

