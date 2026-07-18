<?php

namespace App\Http\Requests\Api\V1\Devices;

use Illuminate\Foundation\Http\FormRequest;

final class DeleteDeviceRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('devices:manage'); }

    public function rules(): array { return []; }
}

