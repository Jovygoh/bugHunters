<?php

namespace App\Http\Requests\Api\V1\Incidents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('incidents:manage'); }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'employee_id' => ['sometimes', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'device_id' => ['sometimes', 'uuid', Rule::exists('devices', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'ai_tool_id' => ['sometimes', 'uuid', Rule::exists('organization_ai_tools', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'policy_id' => ['sometimes', 'uuid', Rule::exists('policies', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'risk' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'action' => ['sometimes', 'string', 'max:100'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'timestamp' => ['sometimes', 'date', 'before_or_equal:now'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'assigned_to' => ['sometimes', 'nullable', 'uuid', Rule::exists('users', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
        ];
    }
}

