<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceAssignment extends UuidModel
{
    protected $table = 'device_assignments';

    protected $fillable = [
            'organization_id',
            'device_id',
            'employee_id',
            'assigned_by',
            'assigned_at',
            'unassigned_at',
            'assignment_type',
            'reason',
        ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function device(): BelongsTo { return $this->belongsTo(Device::class, 'device_id'); }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class, 'employee_id'); }
}

