<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DepartmentClosure extends UuidModel
{
    protected $table = 'department_closure';

    protected $fillable = [
            'organization_id',
            'ancestor_department_id',
            'descendant_department_id',
            'depth',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function ancestorDepartment(): BelongsTo { return $this->belongsTo(Department::class, 'ancestor_department_id'); }

    public function descendantDepartment(): BelongsTo { return $this->belongsTo(Department::class, 'descendant_department_id'); }
}

