<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Employee extends UuidModel
{
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
            'organization_id',
            'user_id',
            'department_id',
            'manager_employee_id',
            'employee_number',
            'first_name',
            'last_name',
            'display_name',
            'work_email',
            'normalized_work_email',
            'job_title',
            'employment_type',
            'status',
            'risk_level',
            'hired_at',
            'terminated_at',
            'metadata',
        ];

    protected function casts(): array
    {
        return [
            'hired_at' => 'date',
            'terminated_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }

    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }

    public function managerEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'manager_employee_id'); }

    public function directReports(): HasMany { return $this->hasMany(Employee::class, 'manager_employee_id'); }

    public function devices(): BelongsToMany { return $this->belongsToMany(Device::class, 'device_assignments')->withPivot(['id', 'organization_id', 'assigned_by', 'assigned_at', 'unassigned_at', 'assignment_type', 'reason'])->withTimestamps(); }

    public function discoveryObservations(): HasMany { return $this->hasMany(DiscoveryObservation::class); }

    public function incidents(): HasMany { return $this->hasMany(Incident::class); }
}

