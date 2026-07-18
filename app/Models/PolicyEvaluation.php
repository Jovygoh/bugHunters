<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolicyEvaluation extends UuidModel
{
    protected $table = 'policy_evaluations';

    protected $fillable = [
            'organization_id',
            'employee_id',
            'device_id',
            'organization_ai_tool_id',
            'data_asset_id',
            'discovery_observation_id',
            'requested_action',
            'decision',
            'reason_code',
            'risk_score',
            'context',
            'context_hash',
            'obligations',
            'correlation_id',
            'evaluation_duration_us',
            'engine_version',
            'evaluated_at',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'risk_score' => 'decimal:2',
            'context' => 'array',
            'obligations' => 'array',
            'evaluation_duration_us' => 'integer',
            'evaluated_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class, 'employee_id'); }

    public function device(): BelongsTo { return $this->belongsTo(Device::class, 'device_id'); }

    public function organizationAiTool(): BelongsTo { return $this->belongsTo(OrganizationAiTool::class, 'organization_ai_tool_id'); }

    public function dataAsset(): BelongsTo { return $this->belongsTo(DataAsset::class, 'data_asset_id'); }

    public function discoveryObservation(): BelongsTo { return $this->belongsTo(DiscoveryObservation::class, 'discovery_observation_id'); }

    public function matches(): HasMany { return $this->hasMany(PolicyEvaluationMatch::class); }

    public function incident(): HasOne { return $this->hasOne(Incident::class); }
}

