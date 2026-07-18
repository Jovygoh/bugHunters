<?php

namespace App\Http\Requests\Api\V1\Devices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('devices:manage');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;
        $device = $this->route('device');

        return [
            'device_name' => ['sometimes', 'string', 'max:255'],
            'device_id' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('devices', 'device_uuid')->ignore($device)->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'employee_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'operating_system' => ['sometimes', 'string', 'max:100'],
            'os_version' => ['sometimes', 'nullable', 'string', 'max:100'],
            'last_seen_at' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::in(['pending', 'verified', 'revoked', 'retired'])],
            'device_type' => ['sometimes', 'string', 'max:50'],
            'ownership_type' => ['sometimes', Rule::in(['corporate', 'personal', 'contractor'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}

