<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'incident_number' => $this->incident_number,
            'employee_id' => $this->employee_id,
            'device_id' => $this->device_id,
            'ai_tool_id' => $this->organization_ai_tool_id,
            'policy_id' => $this->policy_id,
            'risk' => $this->risk_score,
            'action' => $this->action,
            'metadata' => $this->metadata,
            'timestamp' => $this->detected_at?->toISOString(),
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'employee' => $this->whenLoaded('employee', fn () => ['id' => $this->employee->id, 'display_name' => $this->employee->display_name]),
            'device' => $this->whenLoaded('device', fn () => ['id' => $this->device->id, 'device_name' => $this->device->hostname]),
            'ai_tool' => $this->whenLoaded('organizationAiTool', fn () => ['id' => $this->organizationAiTool->id, 'tool_name' => $this->organizationAiTool->display_name]),
            'policy' => $this->whenLoaded('policy', fn () => ['id' => $this->policy->id, 'name' => $this->policy->name, 'decision' => $this->policy->default_effect]),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

