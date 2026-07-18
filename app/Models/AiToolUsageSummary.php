<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiToolUsageSummary extends UuidModel
{
    protected $table = 'ai_tool_usage_summaries';

    protected $fillable = [
            'organization_id',
            'organization_ai_tool_id',
            'metric_date',
            'unique_employee_count',
            'unique_device_count',
            'observation_count',
            'bytes_sent',
            'violation_count',
            'incident_count',
        ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'unique_employee_count' => 'integer',
            'unique_device_count' => 'integer',
            'observation_count' => 'integer',
            'bytes_sent' => 'integer',
            'violation_count' => 'integer',
            'incident_count' => 'integer',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function organizationAiTool(): BelongsTo { return $this->belongsTo(OrganizationAiTool::class, 'organization_ai_tool_id'); }
}

