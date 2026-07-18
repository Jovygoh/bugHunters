<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscoveryScan extends UuidModel
{
    protected $table = 'discovery_scans';

    protected $fillable = [
            'organization_id',
            'discovery_source_id',
            'initiated_by',
            'status',
            'scan_type',
            'started_at',
            'completed_at',
            'records_received',
            'records_processed',
            'findings_created',
            'error_count',
            'error_summary',
            'parameters',
        ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'records_received' => 'integer',
            'records_processed' => 'integer',
            'findings_created' => 'integer',
            'error_count' => 'integer',
            'error_summary' => 'array',
            'parameters' => 'array',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function discoverySource(): BelongsTo { return $this->belongsTo(DiscoverySource::class, 'discovery_source_id'); }

    public function observations(): HasMany { return $this->hasMany(DiscoveryObservation::class); }
}

