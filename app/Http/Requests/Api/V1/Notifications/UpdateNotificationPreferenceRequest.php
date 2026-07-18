<?php

namespace App\Http\Requests\Api\V1\Notifications;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('notifications:view'); }

    public function rules(): array
    {
        return [
            'notification_type' => ['required', Rule::in(['new_ai_tool_detected', 'high_risk_incident', 'policy_violation', 'device_suspicious'])],
            'channel' => ['required', Rule::in(['database', 'email'])],
            'is_enabled' => ['required', 'boolean'],
            'minimum_severity' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
            'quiet_hours' => ['nullable', 'array'],
        ];
    }
}

