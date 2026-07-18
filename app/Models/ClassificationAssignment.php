<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ClassificationAssignment extends UuidModel
{
    protected $table = 'classification_assignments';

    protected $fillable = [
            'organization_id',
            'data_asset_id',
            'classification_level_id',
            'assigned_by_user_id',
            'assignment_source',
            'confidence',
            'reason',
            'evidence',
            'effective_at',
            'superseded_at',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:4',
            'evidence' => 'array',
            'effective_at' => 'datetime',
            'superseded_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function dataAsset(): BelongsTo { return $this->belongsTo(DataAsset::class, 'data_asset_id'); }

    public function classificationLevel(): BelongsTo { return $this->belongsTo(ClassificationLevel::class, 'classification_level_id'); }

    public function assignedByUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by_user_id'); }
}

