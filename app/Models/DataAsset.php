<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DataAsset extends UuidModel
{
    use SoftDeletes;

    protected $table = 'data_assets';

    protected $fillable = [
            'organization_id',
            'department_id',
            'owner_employee_id',
            'current_classification_level_id',
            'external_id',
            'source_system',
            'asset_type',
            'name',
            'location',
            'location_hash',
            'status',
            'contains_personal_data',
            'contains_regulated_data',
            'metadata',
            'last_scanned_at',
        ];

    protected function casts(): array
    {
        return [
            'contains_personal_data' => 'boolean',
            'contains_regulated_data' => 'boolean',
            'metadata' => 'array',
            'last_scanned_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }

    public function ownerEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'owner_employee_id'); }

    public function currentClassificationLevel(): BelongsTo { return $this->belongsTo(ClassificationLevel::class, 'current_classification_level_id'); }

    public function classificationAssignments(): HasMany { return $this->hasMany(ClassificationAssignment::class); }

    public function policyEvaluations(): HasMany { return $this->hasMany(PolicyEvaluation::class); }
}

