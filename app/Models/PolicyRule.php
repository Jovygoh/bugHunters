<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolicyRule extends UuidModel
{
    protected $table = 'policy_rules';

    protected $fillable = [
            'organization_id',
            'policy_version_id',
            'name',
            'sequence',
            'effect',
            'condition_mode',
            'reason_code',
            'message',
            'obligations',
            'is_enabled',
        ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'obligations' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function policyVersion(): BelongsTo { return $this->belongsTo(PolicyVersion::class, 'policy_version_id'); }

    public function conditions(): HasMany { return $this->hasMany(PolicyRuleCondition::class); }

    public function evaluationMatches(): HasMany { return $this->hasMany(PolicyEvaluationMatch::class); }
}

