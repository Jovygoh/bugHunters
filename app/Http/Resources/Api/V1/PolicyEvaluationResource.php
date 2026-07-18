<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PolicyEvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'decision' => $this->decision,
            'reason_code' => $this->reason_code,
            'employee_id' => $this->employee_id,
            'ai_tool_id' => $this->organization_ai_tool_id,
            'data_asset_id' => $this->data_asset_id,
            'requested_action' => $this->requested_action,
            'risk_score' => $this->risk_score,
            'obligations' => $this->obligations,
            'correlation_id' => $this->correlation_id,
            'engine_version' => $this->engine_version,
            'evaluated_at' => $this->evaluated_at?->toISOString(),
        ];
    }
}

