<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Organization extends UuidModel
{
    use SoftDeletes;

    protected $table = 'organizations';

    protected $fillable = [
            'name',
            'slug',
            'status',
            'default_timezone',
            'default_locale',
            'settings',
            'retention_settings',
        ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'retention_settings' => 'array',
        ];
    }

    public function users(): HasMany { return $this->hasMany(User::class); }

    public function employees(): HasMany { return $this->hasMany(Employee::class); }

    public function departments(): HasMany { return $this->hasMany(Department::class); }

    public function devices(): HasMany { return $this->hasMany(Device::class); }

    public function aiTools(): HasMany { return $this->hasMany(OrganizationAiTool::class); }

    public function policies(): HasMany { return $this->hasMany(Policy::class); }

    public function incidents(): HasMany { return $this->hasMany(Incident::class); }

    public function auditLogs(): HasMany { return $this->hasMany(AuditLog::class); }

    public function violationTypes(): HasMany { return $this->hasMany(ViolationType::class); }
}
