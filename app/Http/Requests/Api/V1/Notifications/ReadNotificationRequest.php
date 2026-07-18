<?php

namespace App\Http\Requests\Api\V1\Notifications;

use Illuminate\Foundation\Http\FormRequest;

final class ReadNotificationRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->tokenCan('notifications:view'); }

    public function rules(): array { return []; }
}

