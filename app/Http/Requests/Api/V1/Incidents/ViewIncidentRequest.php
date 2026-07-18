<?php

namespace App\Http\Requests\Api\V1\Incidents;

use Illuminate\Foundation\Http\FormRequest;

final class ViewIncidentRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('incidents:view'); }

    public function rules(): array { return []; }
}

