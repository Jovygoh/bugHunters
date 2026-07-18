<?php

namespace App\Http\Requests\Api\V1\AiTools;

use Illuminate\Foundation\Http\FormRequest;

final class DeleteAiToolRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('ai-tools:manage'); }

    public function rules(): array { return []; }
}

