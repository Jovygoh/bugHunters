<?php

namespace App\Http\Requests\Api\V1\DataClassification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexViolationTypeRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('classifications:view'); }

    public function rules(): array
    {
        return [
            'severity' => ['sometimes', Rule::in(['high', 'critical'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array { return $this->safe()->only(['severity', 'status']); }
}

