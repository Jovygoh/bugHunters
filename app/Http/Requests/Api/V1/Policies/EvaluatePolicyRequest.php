<?php

namespace App\Http\Requests\Api\V1\Policies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class EvaluatePolicyRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('policies:view'); }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'employee_id' => ['required', 'uuid', Rule::exists('employees', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'ai_tool_id' => ['required', 'uuid', Rule::exists('organization_ai_tools', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'classification_level_id' => ['required', 'uuid', Rule::exists('classification_levels', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'data_asset_id' => ['nullable', 'uuid', Rule::exists('data_assets', 'id')->where('organization_id', $organizationId)->whereNull('deleted_at')],
            'action' => ['required', 'string', 'max:100'],
            'correlation_id' => ['required', 'uuid'],
        ];
    }
}

