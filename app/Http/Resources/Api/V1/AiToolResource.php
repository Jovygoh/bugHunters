<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AiToolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tool_name' => $this->display_name,
            'domain' => $this->primary_domain,
            'category' => $this->category,
            'risk_level' => $this->risk_level,
            'status' => $this->status,
            'description' => $this->description,
            'approval_status' => $this->approval_status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

