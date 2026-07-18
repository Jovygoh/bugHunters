<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscoveryFinding extends UuidModel
{
    use SoftDeletes;

    protected $table = 'discovery_findings';

    protected $fillable = [
            'organization_id',
            'observation_id',
            'employee_id',
            'device_id',
            'organization_ai_tool_id',
            'incident_id',
            'finding_type',
            'detected_domain',
            'severity',
            'status',
            'title',
            'description',
            'risk_score',
            'first_observed_at',
            'last_observed_at',
            'occurrence_count',
            'assigned_to',
            'resolved_by',
            'resolved_at',
            'resolution_code',
            'resolution_notes',
            'deduplication_key',
        ];

    protected function casts(): array
    {
        return [
            'risk_score' => 'decimal:2',
            'first_observed_at' => 'datetime',
            'last_observed_at' => 'datetime',
            'occurrence_count' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function observation(): BelongsTo { return $this->belongsTo(DiscoveryObservation::class, 'observation_id'); }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class, 'employee_id'); }

    public function device(): BelongsTo { return $this->belongsTo(Device::class, 'device_id'); }

    public function organizationAiTool(): BelongsTo { return $this->belongsTo(OrganizationAiTool::class, 'organization_ai_tool_id'); }

    public function incident(): BelongsTo { return $this->belongsTo(Incident::class, 'incident_id'); }

    public function events(): HasMany { return $this->hasMany(DiscoveryFindingEvent::class); }
}
