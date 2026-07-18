<?php

namespace App\Http\Requests\Api\V1\Audit;

use Illuminate\Foundation\Http\FormRequest;

final class ShowAuditLogRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('audit:view'); }
    public function rules(): array { return []; }
}
