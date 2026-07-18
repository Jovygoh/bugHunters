<?php

namespace App\Http\Requests\Api\V1\Discovery;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUnknownAiToolDetectionStatusRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('discovery:manage'); }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['open', 'investigating', 'ignored', 'resolved'])],
            'resolution_code' => ['required_if:status,resolved', 'nullable', 'string', 'max:50'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

