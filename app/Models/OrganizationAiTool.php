<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrganizationAiTool extends UuidModel
{
    use SoftDeletes;

    protected $table = 'organization_ai_tools';

    protected $fillable = [
            'organization_id',
            'catalog_ai_tool_id',
            'display_name',
            'primary_domain',
            'category',
            'description',
            'status',
            'approval_status',
            'risk_level',
            'risk_score',
            'approved_at',
            'approved_by',
            'blocked_at',
            'blocked_by',
            'reviewed_at',
            'reviewed_by',
            'allowed_classification_level_id',
            'settings',
            'risk_assessment',
        ];

    protected function casts(): array
    {
        return [
            'risk_score' => 'decimal:2',
            'approved_at' => 'datetime',
            'blocked_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'settings' => 'array',
            'risk_assessment' => 'array',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function catalogAiTool(): BelongsTo { return $this->belongsTo(AiToolCatalog::class, 'catalog_ai_tool_id'); }

    public function allowedClassificationLevel(): BelongsTo { return $this->belongsTo(ClassificationLevel::class, 'allowed_classification_level_id'); }

    public function endpoints(): HasMany { return $this->hasMany(OrganizationAiToolEndpoint::class); }

    public function observations(): HasMany { return $this->hasMany(DiscoveryObservation::class); }

    public function findings(): HasMany { return $this->hasMany(DiscoveryFinding::class); }

    public function evaluations(): HasMany { return $this->hasMany(PolicyEvaluation::class); }

    public function incidents(): HasMany { return $this->hasMany(Incident::class); }
}
