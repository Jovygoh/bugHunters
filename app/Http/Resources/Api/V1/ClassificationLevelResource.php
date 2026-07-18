<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ClassificationLevelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'rank' => $this->rank,
            'severity' => $this->severity,
            'description' => $this->description,
            'color' => $this->color,
            'handling_rules' => $this->handling_rules,
            'scheme' => $this->whenLoaded('classificationScheme', fn () => [
                'id' => $this->classificationScheme->id,
                'name' => $this->classificationScheme->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

