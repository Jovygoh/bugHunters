<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Policy extends UuidModel
{
    use SoftDeletes;

    protected $table = 'policies';

    protected $fillable = [
            'organization_id',
            'active_version_id',
            'name',
            'code',
            'description',
            'category',
            'status',
            'priority',
            'is_mandatory',
            'default_effect',
            'effective_from',
            'effective_until',
            'created_by',
            'updated_by',
        ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_mandatory' => 'boolean',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function activeVersion(): BelongsTo { return $this->belongsTo(PolicyVersion::class, 'active_version_id'); }

    public function versions(): HasMany { return $this->hasMany(PolicyVersion::class); }

    public function scopes(): HasMany { return $this->hasMany(PolicyScope::class); }

    public function evaluationMatches(): HasMany { return $this->hasMany(PolicyEvaluationMatch::class); }

    public function incidents(): HasMany { return $this->hasMany(Incident::class); }
}
