<?php

namespace App\Http\Requests\Api\V1\Policies;

use Illuminate\Foundation\Http\FormRequest;

final class PublishPolicyRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('policies:publish'); }

    public function rules(): array { return []; }
}

