<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_name' => $this->hostname,
            'device_id' => $this->device_uuid,
            'employee_id' => $this->current_employee_id,
            'operating_system' => $this->operating_system,
            'os_version' => $this->os_version,
            'last_seen_at' => $this->last_seen_at?->toISOString(),
            'status' => $this->registration_status,
            'device_type' => $this->device_type,
            'ownership_type' => $this->ownership_type,
            'compliance_status' => $this->compliance_status,
            'trust_level' => $this->trust_level,
            'registered_at' => $this->registered_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
            'metadata' => $this->metadata,
            'employee' => $this->whenLoaded('currentEmployee', fn () => [
                'id' => $this->currentEmployee->id,
                'display_name' => $this->currentEmployee->display_name,
                'work_email' => $this->currentEmployee->work_email,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

