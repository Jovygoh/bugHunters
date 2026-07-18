<?php

namespace App\Http\Requests\Api\V1\Discovery;

use Illuminate\Foundation\Http\FormRequest;

final class ShowUnknownAiToolDetectionRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('discovery:view'); }

    public function rules(): array { return []; }
}

