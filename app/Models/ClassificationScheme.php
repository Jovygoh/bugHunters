<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ClassificationScheme extends UuidModel
{
    use SoftDeletes;

    protected $table = 'classification_schemes';

    protected $fillable = [
            'organization_id',
            'name',
            'description',
            'status',
            'is_default',
        ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function levels(): HasMany { return $this->hasMany(ClassificationLevel::class); }
}

