<?php

namespace App\Http\Requests\Api\V1\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

final class MonthlyStatisticsRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('dashboard:view'); }

    public function rules(): array
    {
        return ['months' => ['sometimes', 'integer', 'min:1', 'max:36']];
    }
}
