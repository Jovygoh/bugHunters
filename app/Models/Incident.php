<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Incident extends UuidModel
{
    use SoftDeletes;

    protected $table = 'incidents';

    protected $fillable = [
            'organization_id',
            'incident_number',
            'title',
            'description',
            'incident_type',
            'severity',
            'status',
            'priority',
            'employee_id',
            'device_id',
            'organization_ai_tool_id',
            'policy_evaluation_id',
            'policy_id',
            'discovery_finding_id',
            'assigned_to',
            'assigned_team',
            'reported_by',
            'source',
            'action',
            'metadata',
            'risk_score',
            'detected_at',
            'acknowledged_at',
            'resolved_at',
            'closed_at',
            'resolution_code',
            'resolution_summary',
            'sla_due_at',
            'lock_version',
        ];

    protected function casts(): array
    {
        return [
            'incident_number' => 'integer',
            'risk_score' => 'decimal:2',
            'metadata' => 'array',
            'detected_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'sla_due_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class, 'employee_id'); }

    public function device(): BelongsTo { return $this->belongsTo(Device::class, 'device_id'); }

    public function organizationAiTool(): BelongsTo { return $this->belongsTo(OrganizationAiTool::class, 'organization_ai_tool_id'); }

    public function policyEvaluation(): BelongsTo { return $this->belongsTo(PolicyEvaluation::class, 'policy_evaluation_id'); }

    public function policy(): BelongsTo { return $this->belongsTo(Policy::class); }

    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reported_by'); }

    public function discoveryFinding(): BelongsTo { return $this->belongsTo(DiscoveryFinding::class, 'discovery_finding_id'); }

    public function events(): HasMany { return $this->hasMany(IncidentEvent::class); }

    public function comments(): HasMany { return $this->hasMany(IncidentComment::class); }

    public function evidence(): HasMany { return $this->hasMany(IncidentEvidence::class); }

    public function notifications(): HasMany { return $this->hasMany(Notification::class); }
}
