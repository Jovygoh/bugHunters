<?php

namespace App\Http\Requests\Api\V1\Devices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('devices:manage');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'device_name' => ['required', 'string', 'max:255'],
            'device_id' => ['required', 'string', 'max:255', Rule::unique('devices', 'device_uuid')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'employee_id' => ['required', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'operating_system' => ['required', 'string', 'max:100'],
            'os_version' => ['nullable', 'string', 'max:100'],
            'last_seen_at' => ['required', 'date'],
            'status' => ['sometimes', Rule::in(['pending', 'verified'])],
            'device_type' => ['sometimes', 'string', 'max:50'],
            'ownership_type' => ['sometimes', Rule::in(['corporate', 'personal', 'contractor'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

