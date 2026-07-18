<?php

namespace App\Http\Requests\Api\V1\Notifications;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexNotificationRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('notifications:view'); }

    public function rules(): array
    {
        return [
            'notification_type' => ['sometimes', Rule::in(['new_ai_tool_detected', 'high_risk_incident', 'policy_violation', 'device_suspicious'])],
            'severity' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'read_status' => ['sometimes', Rule::in(['read', 'unread'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->safe()->only(['notification_type', 'severity', 'read_status']);
    }
}

