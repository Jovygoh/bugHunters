<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscoverySource extends UuidModel
{
    use SoftDeletes;

    protected $table = 'discovery_sources';

    protected $fillable = [
            'organization_id',
            'name',
            'source_type',
            'status',
            'external_key',
            'configuration',
            'credential_ref',
            'last_sync_at',
        ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'last_sync_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function scans(): HasMany { return $this->hasMany(DiscoveryScan::class); }

    public function observations(): HasMany { return $this->hasMany(DiscoveryObservation::class); }
}

