<?php

namespace App\Http\Requests\Api\V1\Incidents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TransitionIncidentRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('incidents:manage'); }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['triaged', 'investigating', 'contained', 'resolved', 'closed'])]];
    }
}

