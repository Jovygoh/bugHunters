<?php

namespace App\Http\Requests\Api\V1\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

final class DashboardSummaryRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('dashboard:view'); }

    public function rules(): array
    {
        return ['active_days' => ['sometimes', 'integer', 'min:1', 'max:365']];
    }
}
