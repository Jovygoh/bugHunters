<?php

namespace App\Http\Requests\Api\V1\AiTools;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexAiToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('ai-tools:view');
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:100'],
            'risk_level' => ['sometimes', Rule::in(['unknown', 'low', 'medium', 'high', 'critical'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'blocked'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->safe()->only(['search', 'category', 'risk_level', 'status']);
    }
}

