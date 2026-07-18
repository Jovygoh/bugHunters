<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'category' => $this->category,
            'decision' => $this->default_effect,
            'status' => $this->status,
            'priority' => $this->priority,
            'is_mandatory' => $this->is_mandatory,
            'effective_from' => $this->effective_from?->toISOString(),
            'effective_until' => $this->effective_until?->toISOString(),
            'active_version' => $this->whenLoaded('activeVersion', fn () => [
                'id' => $this->activeVersion->id,
                'version_number' => $this->activeVersion->version_number,
                'status' => $this->activeVersion->status,
                'published_at' => $this->activeVersion->published_at?->toISOString(),
            ]),
            'scopes' => $this->whenLoaded('scopes', fn () => [
                'ai_tool_ids' => $this->scopes->pluck('organization_ai_tool_id')->filter()->values(),
                'classification_level_ids' => $this->scopes->pluck('classification_level_id')->filter()->values(),
                'role_ids' => $this->scopes->pluck('role_id')->filter()->values(),
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

