<?php

namespace App\Http\Requests\Api\V1\Discovery;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexUnknownAiToolDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('discovery:view');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'domain' => ['sometimes', 'string', 'max:253'],
            'employee_id' => ['sometimes', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'device_id' => ['sometimes', 'uuid', Rule::exists('devices', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'status' => ['sometimes', Rule::in(['open', 'investigating', 'ignored', 'resolved'])],
            'severity' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'detected_from' => ['sometimes', 'date'],
            'detected_to' => ['sometimes', 'date', 'after_or_equal:detected_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->safe()->only([
            'domain', 'employee_id', 'device_id', 'status', 'severity', 'detected_from', 'detected_to',
        ]);
    }
}

