<?php

namespace App\Http\Requests\Api\V1\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IncidentTrendRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('dashboard:view'); }

    public function rules(): array
    {
        return [
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'interval' => ['sometimes', Rule::in(['day', 'month'])],
        ];
    }
}
