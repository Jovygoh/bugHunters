<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Department extends UuidModel
{
    use SoftDeletes;

    protected $table = 'departments';

    protected $fillable = [
            'organization_id',
            'parent_department_id',
            'manager_employee_id',
            'name',
            'code',
            'description',
            'status',
            'metadata',
        ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function parentDepartment(): BelongsTo { return $this->belongsTo(Department::class, 'parent_department_id'); }

    public function managerEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'manager_employee_id'); }

    public function children(): HasMany { return $this->hasMany(Department::class, 'parent_department_id'); }

    public function employees(): HasMany { return $this->hasMany(Employee::class); }

    public function devices(): HasMany { return $this->hasMany(Device::class); }

    public function ancestors(): BelongsToMany { return $this->belongsToMany(Department::class, 'department_closure', 'descendant_department_id', 'ancestor_department_id')->withPivot(['id', 'depth']); }

    public function descendants(): BelongsToMany { return $this->belongsToMany(Department::class, 'department_closure', 'ancestor_department_id', 'descendant_department_id')->withPivot(['id', 'depth']); }
}

