<?php

namespace App\Http\Requests\Api\V1\AiTools;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAiToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('ai-tools:manage');
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;
        $aiToolId = $this->route('aiTool');

        return [
            'tool_name' => ['sometimes', 'string', 'max:200'],
            'domain' => [
                'sometimes', 'string', 'max:253',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i',
                Rule::unique('organization_ai_tools', 'primary_domain')->ignore($aiToolId)->where('organization_id', $organizationId)->whereNull('deleted_at'),
            ],
            'category' => ['sometimes', 'string', 'max:100'],
            'risk_level' => ['sometimes', Rule::in(['unknown', 'low', 'medium', 'high', 'critical'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'blocked'])],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}

