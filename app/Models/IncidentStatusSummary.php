<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IncidentStatusSummary extends UuidModel
{
    protected $table = 'incident_status_summaries';

    protected $fillable = [
            'organization_id',
            'metric_date',
            'status',
            'severity',
            'incident_count',
            'average_resolution_seconds',
        ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'incident_count' => 'integer',
            'average_resolution_seconds' => 'integer',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }
}

