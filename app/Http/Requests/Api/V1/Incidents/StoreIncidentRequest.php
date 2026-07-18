<?php

namespace App\Http\Requests\Api\V1\Incidents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('incidents:manage'); }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;
        $employeeId = $this->input('employee_id');

        return [
            'employee_id' => ['required', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'device_id' => [
                'required', 'uuid',
                Rule::exists('devices', 'id')->where('organization_id', $organizationId)->where('current_employee_id', $employeeId)->whereNull('deleted_at'),
            ],
            'ai_tool_id' => ['required', 'uuid', Rule::exists('organization_ai_tools', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'policy_id' => ['required', 'uuid', Rule::exists('policies', 'id')->where('organization_id', $organizationId)->where('status', 'published')->whereNull('deleted_at')],
            'risk' => ['required', 'numeric', 'min:0', 'max:100'],
            'action' => ['required', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
            'timestamp' => ['required', 'date', 'before_or_equal:now'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}

