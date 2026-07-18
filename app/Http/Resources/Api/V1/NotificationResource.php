<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->notification_type,
            'severity' => $this->severity,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'incident_id' => $this->incident_id,
            'policy_evaluation_id' => $this->policy_evaluation_id,
            'read_at' => $this->read_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

