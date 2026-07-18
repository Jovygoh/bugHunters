<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Device extends UuidModel
{
    use SoftDeletes;

    protected $table = 'devices';

    protected $fillable = [
            'organization_id',
            'current_employee_id',
            'department_id',
            'device_uuid',
            'hostname',
            'serial_number',
            'asset_tag',
            'device_type',
            'operating_system',
            'os_version',
            'ownership_type',
            'registration_status',
            'compliance_status',
            'trust_level',
            'registered_at',
            'verified_at',
            'last_seen_at',
            'revoked_at',
            'retired_at',
            'metadata',
        ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'revoked_at' => 'datetime',
            'retired_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function currentEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'current_employee_id'); }

    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }

    public function assignments(): HasMany { return $this->hasMany(DeviceAssignment::class); }

    public function postureSnapshots(): HasMany { return $this->hasMany(DevicePostureSnapshot::class); }

    public function discoveryObservations(): HasMany { return $this->hasMany(DiscoveryObservation::class); }

    public function policyEvaluations(): HasMany { return $this->hasMany(PolicyEvaluation::class); }

    public function incidents(): HasMany { return $this->hasMany(Incident::class); }
}

