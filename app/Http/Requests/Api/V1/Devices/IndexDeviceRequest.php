<?php

namespace App\Http\Requests\Api\V1\Devices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('devices:view');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'employee_id' => ['sometimes', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'operating_system' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(['pending', 'verified', 'revoked', 'retired'])],
            'compliance_status' => ['sometimes', Rule::in(['compliant', 'noncompliant', 'unknown'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        $filters = $this->safe()->only(['search', 'operating_system', 'compliance_status']);
        if ($this->filled('employee_id')) $filters['current_employee_id'] = $this->input('employee_id');
        if ($this->filled('status')) $filters['registration_status'] = $this->input('status');

        return $filters;
    }
}

