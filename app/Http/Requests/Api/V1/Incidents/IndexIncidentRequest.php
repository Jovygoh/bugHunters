<?php

namespace App\Http\Requests\Api\V1\Incidents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexIncidentRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('incidents:view'); }

    public function rules(): array
    {
        return [
            'employee_id' => ['sometimes', 'uuid'],
            'device_id' => ['sometimes', 'uuid'],
            'ai_tool_id' => ['sometimes', 'uuid'],
            'policy_id' => ['sometimes', 'uuid'],
            'severity' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['sometimes', Rule::in(['open', 'triaged', 'investigating', 'contained', 'resolved', 'closed'])],
            'priority' => ['sometimes', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'action' => ['sometimes', 'string', 'max:100'],
            'detected_from' => ['sometimes', 'date'],
            'detected_to' => ['sometimes', 'date', 'after_or_equal:detected_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        $filters = $this->safe()->only([
            'employee_id', 'device_id', 'policy_id', 'severity', 'status', 'priority',
            'action', 'detected_from', 'detected_to',
        ]);
        if ($this->filled('ai_tool_id')) $filters['organization_ai_tool_id'] = $this->input('ai_tool_id');

        return $filters;
    }
}

