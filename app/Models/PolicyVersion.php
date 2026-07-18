<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolicyVersion extends UuidModel
{
    protected $table = 'policy_versions';

    protected $fillable = [
            'organization_id',
            'policy_id',
            'version_number',
            'status',
            'definition_schema',
            'definition',
            'definition_hash',
            'change_summary',
            'created_by',
            'published_by',
            'published_at',
            'retired_at',
        ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'definition' => 'array',
            'published_at' => 'datetime',
            'retired_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function policy(): BelongsTo { return $this->belongsTo(Policy::class, 'policy_id'); }

    public function rules(): HasMany { return $this->hasMany(PolicyRule::class); }

    public function evaluationMatches(): HasMany { return $this->hasMany(PolicyEvaluationMatch::class); }
}

