<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolicyRuleCondition extends UuidModel
{
    protected $table = 'policy_rule_conditions';

    protected $fillable = [
            'organization_id',
            'policy_rule_id',
            'condition_group',
            'sequence',
            'attribute',
            'operator',
            'value_type',
            'value',
            'negated',
        ];

    protected function casts(): array
    {
        return [
            'condition_group' => 'integer',
            'sequence' => 'integer',
            'value' => 'array',
            'negated' => 'boolean',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function policyRule(): BelongsTo { return $this->belongsTo(PolicyRule::class, 'policy_rule_id'); }
}

