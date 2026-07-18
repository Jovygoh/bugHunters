<?php

namespace App\Http\Requests\Api\V1\Devices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class HeartbeatDeviceRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('devices:manage'); }

    public function rules(): array
    {
        return [
            'last_seen_at' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::in(['pending', 'verified', 'revoked', 'retired'])],
        ];
    }
}

