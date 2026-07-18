<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ClassificationLevel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'classification_levels';

    protected $fillable = [
            'organization_id',
            'classification_scheme_id',
            'name',
            'code',
            'rank',
            'severity',
            'color',
            'description',
            'handling_rules',
        ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
            'handling_rules' => 'array',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function classificationScheme(): BelongsTo { return $this->belongsTo(ClassificationScheme::class, 'classification_scheme_id'); }

    public function dataAssets(): HasMany { return $this->hasMany(DataAsset::class, 'current_classification_level_id'); }

    public function assignments(): HasMany { return $this->hasMany(ClassificationAssignment::class); }

    public function policyScopes(): HasMany { return $this->hasMany(PolicyScope::class); }
}

