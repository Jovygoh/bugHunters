<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->actor_user_id,
            'user' => $this->actor_identifier,
            'action' => $this->action,
            'module' => $this->module,
            'outcome' => $this->outcome,
            'description' => $this->description,
            'target' => ['type' => $this->auditable_type, 'id' => $this->auditable_id],
            'metadata' => $this->metadata,
            'timestamp' => $this->occurred_at?->toISOString(),
        ];
    }
}
