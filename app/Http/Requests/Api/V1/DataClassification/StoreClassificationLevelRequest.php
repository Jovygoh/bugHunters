<?php

namespace App\Http\Requests\Api\V1\DataClassification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreClassificationLevelRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('classifications:manage'); }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::in(['Public', 'Internal', 'Confidential', 'Highly Confidential'])],
            'description' => ['nullable', 'string', 'max:5000'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'handling_rules' => ['nullable', 'array'],
        ];
    }
}

