<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscoveryObservation extends UuidModel
{
    protected $table = 'discovery_observations';

    protected $fillable = [
            'organization_id',
            'discovery_scan_id',
            'discovery_source_id',
            'employee_id',
            'device_id',
            'organization_ai_tool_id',
            'external_event_id',
            'observation_type',
            'destination_domain',
            'destination_url_hash',
            'application_name',
            'action',
            'bytes_sent',
            'bytes_received',
            'classification_hint',
            'matched_confidence',
            'risk_score',
            'raw_payload_ref',
            'normalized_attributes',
            'observed_at',
            'ingested_at',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'bytes_sent' => 'integer',
            'bytes_received' => 'integer',
            'matched_confidence' => 'decimal:4',
            'risk_score' => 'decimal:2',
            'normalized_attributes' => 'array',
            'observed_at' => 'datetime',
            'ingested_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function discoveryScan(): BelongsTo { return $this->belongsTo(DiscoveryScan::class, 'discovery_scan_id'); }

    public function discoverySource(): BelongsTo { return $this->belongsTo(DiscoverySource::class, 'discovery_source_id'); }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class, 'employee_id'); }

    public function device(): BelongsTo { return $this->belongsTo(Device::class, 'device_id'); }

    public function organizationAiTool(): BelongsTo { return $this->belongsTo(OrganizationAiTool::class, 'organization_ai_tool_id'); }

    public function finding(): HasOne { return $this->hasOne(DiscoveryFinding::class, 'observation_id'); }

    public function evaluations(): HasMany { return $this->hasMany(PolicyEvaluation::class); }
}

