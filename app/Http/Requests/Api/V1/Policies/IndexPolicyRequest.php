<?php

namespace App\Http\Requests\Api\V1\Policies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexPolicyRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('policies:view'); }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'retired'])],
            'decision' => ['sometimes', Rule::in(['allow', 'warn', 'review', 'block'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        $filters = $this->safe()->only(['search', 'status']);
        if ($this->filled('decision')) $filters['default_effect'] = $this->input('decision');

        return $filters;
    }
}

