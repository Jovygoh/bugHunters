<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolicyScope extends UuidModel
{
    protected $table = 'policy_scopes';

    protected $fillable = [
            'organization_id',
            'policy_id',
            'scope_effect',
            'department_id',
            'employee_id',
            'device_id',
            'organization_ai_tool_id',
            'classification_level_id',
            'role_id',
            'includes_descendants',
        ];

    protected function casts(): array
    {
        return [
            'includes_descendants' => 'boolean',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function policy(): BelongsTo { return $this->belongsTo(Policy::class, 'policy_id'); }

    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class, 'employee_id'); }

    public function device(): BelongsTo { return $this->belongsTo(Device::class, 'device_id'); }

    public function organizationAiTool(): BelongsTo { return $this->belongsTo(OrganizationAiTool::class, 'organization_ai_tool_id'); }

    public function classificationLevel(): BelongsTo { return $this->belongsTo(ClassificationLevel::class, 'classification_level_id'); }

    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
}
