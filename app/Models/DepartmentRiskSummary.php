<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DepartmentRiskSummary extends UuidModel
{
    protected $table = 'department_risk_summaries';

    protected $fillable = [
            'organization_id',
            'department_id',
            'metric_date',
            'employee_count',
            'device_count',
            'ai_tool_count',
            'policy_violation_count',
            'open_incident_count',
            'risk_score',
        ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'employee_count' => 'integer',
            'device_count' => 'integer',
            'ai_tool_count' => 'integer',
            'policy_violation_count' => 'integer',
            'open_incident_count' => 'integer',
            'risk_score' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }
}

