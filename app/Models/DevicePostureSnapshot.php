<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DevicePostureSnapshot extends UuidModel
{
    protected $table = 'device_posture_snapshots';

    protected $fillable = [
            'organization_id',
            'device_id',
            'source',
            'compliance_status',
            'risk_score',
            'is_encrypted',
            'has_screen_lock',
            'has_endpoint_agent',
            'is_os_supported',
            'attributes',
            'observed_at',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'risk_score' => 'decimal:2',
            'is_encrypted' => 'boolean',
            'has_screen_lock' => 'boolean',
            'has_endpoint_agent' => 'boolean',
            'is_os_supported' => 'boolean',
            'attributes' => 'array',
            'observed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function device(): BelongsTo { return $this->belongsTo(Device::class, 'device_id'); }
}

