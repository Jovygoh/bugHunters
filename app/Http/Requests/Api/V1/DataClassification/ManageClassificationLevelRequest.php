<?php

namespace App\Http\Requests\Api\V1\DataClassification;

use Illuminate\Foundation\Http\FormRequest;

final class ManageClassificationLevelRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('classifications:manage'); }

    public function rules(): array { return []; }
}

