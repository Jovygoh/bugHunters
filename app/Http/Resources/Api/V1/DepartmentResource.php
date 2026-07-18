<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'parent_department_id' => $this->parent_department_id,
            'manager_employee_id' => $this->manager_employee_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'employees_count' => $this->whenCounted('employees'),
            'children_count' => $this->whenCounted('children'),
            'parent' => $this->whenLoaded('parentDepartment', fn () => [
                'id' => $this->parentDepartment->id,
                'name' => $this->parentDepartment->name,
                'code' => $this->parentDepartment->code,
            ]),
            'manager' => $this->whenLoaded('managerEmployee', fn () => [
                'id' => $this->managerEmployee->id,
                'display_name' => $this->managerEmployee->display_name,
                'work_email' => $this->managerEmployee->work_email,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

