<?php

namespace App\Http\Requests\Api\V1\Audit;

use Illuminate\Foundation\Http\FormRequest;

final class IndexAuditLogRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('audit:view'); }

    public function rules(): array
    {
        return [
            'actor_user_id' => ['sometimes', 'uuid'],
            'action' => ['sometimes', 'string', 'max:150'],
            'module' => ['sometimes', 'string', 'max:100'],
            'outcome' => ['sometimes', 'in:success,failure'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->safe()->only(['actor_user_id', 'action', 'module', 'outcome', 'from', 'to']);
    }
}
