<?php

namespace App\Http\Requests\Api\V1\Discovery;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUnknownAiToolDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('discovery:manage');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;
        $employeeId = $this->input('employee_id');

        return [
            'domain' => [
                'required', 'string', 'max:253',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i',
                Rule::unique('organization_ai_tools', 'primary_domain')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at'),
            ],
            'employee_id' => [
                'required', 'uuid',
                Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'device_id' => [
                'required', 'uuid',
                Rule::exists('devices', 'id')
                    ->where('organization_id', $organizationId)
                    ->where('current_employee_id', $employeeId)
                    ->whereNull('deleted_at'),
            ],
            'detection_time' => ['required', 'date', 'before_or_equal:now'],
            'status' => ['required', Rule::in(['open', 'investigating', 'ignored'])],
            'risk_score' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}

