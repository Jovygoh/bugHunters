<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolicyComplianceSummary extends UuidModel
{
    protected $table = 'policy_compliance_summaries';

    protected $fillable = [
            'organization_id',
            'policy_id',
            'metric_date',
            'evaluation_count',
            'allow_count',
            'warn_count',
            'approval_required_count',
            'block_count',
            'compliance_rate',
        ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'evaluation_count' => 'integer',
            'allow_count' => 'integer',
            'warn_count' => 'integer',
            'approval_required_count' => 'integer',
            'block_count' => 'integer',
            'compliance_rate' => 'decimal:4',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function policy(): BelongsTo { return $this->belongsTo(Policy::class, 'policy_id'); }
}

