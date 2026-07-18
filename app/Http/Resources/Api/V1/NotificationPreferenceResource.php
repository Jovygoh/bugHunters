<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class NotificationPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notification_type' => $this->notification_type,
            'channel' => $this->channel,
            'is_enabled' => $this->is_enabled,
            'minimum_severity' => $this->minimum_severity,
            'quiet_hours' => $this->quiet_hours,
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

