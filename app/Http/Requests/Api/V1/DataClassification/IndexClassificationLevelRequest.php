<?php

namespace App\Http\Requests\Api\V1\DataClassification;

use Illuminate\Foundation\Http\FormRequest;

final class IndexClassificationLevelRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('classifications:view'); }

    public function rules(): array
    {
        return ['per_page' => ['sometimes', 'integer', 'min:1', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']];
    }
}

