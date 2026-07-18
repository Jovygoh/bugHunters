<?php

namespace App\Http\Requests\Api\V1\Departments;

use Illuminate\Foundation\Http\FormRequest;

final class DeleteDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tokenCan('departments:manage');
    }

    public function rules(): array { return []; }
}

