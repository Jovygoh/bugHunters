<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UnknownAiToolDetectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'domain' => $this->detected_domain,
            'employee_id' => $this->employee_id,
            'device_id' => $this->device_id,
            'detection_time' => $this->last_observed_at?->toISOString(),
            'first_detection_time' => $this->first_observed_at?->toISOString(),
            'status' => $this->status,
            'risk_score' => $this->risk_score,
            'severity' => $this->severity,
            'occurrence_count' => $this->occurrence_count,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'display_name' => $this->employee->display_name,
                'work_email' => $this->employee->work_email,
            ]),
            'device' => $this->whenLoaded('device', fn () => [
                'id' => $this->device->id,
                'device_name' => $this->device->hostname,
                'device_id' => $this->device->device_uuid,
            ]),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'resolution_code' => $this->resolution_code,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

