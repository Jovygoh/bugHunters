<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolicyEvaluationMatch extends UuidModel
{
    protected $table = 'policy_evaluation_matches';

    protected $fillable = [
            'organization_id',
            'policy_evaluation_id',
            'policy_id',
            'policy_version_id',
            'policy_rule_id',
            'matched',
            'effect',
            'priority',
            'match_details',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'matched' => 'boolean',
            'priority' => 'integer',
            'match_details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function policyEvaluation(): BelongsTo { return $this->belongsTo(PolicyEvaluation::class, 'policy_evaluation_id'); }

    public function policy(): BelongsTo { return $this->belongsTo(Policy::class, 'policy_id'); }

    public function policyVersion(): BelongsTo { return $this->belongsTo(PolicyVersion::class, 'policy_version_id'); }

    public function policyRule(): BelongsTo { return $this->belongsTo(PolicyRule::class, 'policy_rule_id'); }
}

