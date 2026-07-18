<?php

namespace App\Http\Requests\Api\V1\Policies;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePolicyRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('policies:manage'); }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;
        $roleExists = Rule::exists('roles', 'id')->where(fn (Builder $query) => $query
            ->where('organization_id', $organizationId)
            ->orWhereNull('organization_id'));

        return [
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:100', Rule::unique('policies')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['sometimes', 'string', 'max:100'],
            'decision' => ['required', Rule::in(['allow', 'warn', 'review', 'block'])],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'is_mandatory' => ['sometimes', 'boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after:effective_from'],
            'ai_tool_ids' => ['required', 'array', 'min:1'],
            'ai_tool_ids.*' => ['uuid', 'distinct', Rule::exists('organization_ai_tools', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'classification_level_ids' => ['required', 'array', 'min:1'],
            'classification_level_ids.*' => ['uuid', 'distinct', Rule::exists('classification_levels', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['uuid', 'distinct', $roleExists],
        ];
    }
}

