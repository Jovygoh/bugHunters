<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DashboardDailyMetric extends UuidModel
{
    protected $table = 'dashboard_daily_metrics';

    protected $fillable = [
            'organization_id',
            'metric_date',
            'active_employees',
            'registered_devices',
            'noncompliant_devices',
            'discovered_ai_tools',
            'blocked_ai_tools',
            'policy_evaluations',
            'blocked_evaluations',
            'open_incidents',
            'critical_incidents',
            'risk_score',
        ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'active_employees' => 'integer',
            'registered_devices' => 'integer',
            'noncompliant_devices' => 'integer',
            'discovered_ai_tools' => 'integer',
            'blocked_ai_tools' => 'integer',
            'policy_evaluations' => 'integer',
            'blocked_evaluations' => 'integer',
            'open_incidents' => 'integer',
            'critical_incidents' => 'integer',
            'risk_score' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }
}

