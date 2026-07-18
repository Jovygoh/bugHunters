<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Role extends UuidModel
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
            'organization_id',
            'name',
            'code',
            'description',
            'is_system',
        ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'user_roles')->withPivot(['id', 'organization_id', 'assigned_by', 'expires_at'])->withTimestamps(); }

    public function permissions(): BelongsToMany { return $this->belongsToMany(Permission::class, 'role_permissions')->withPivot(['id', 'created_at']); }

    public function policyScopes(): HasMany { return $this->hasMany(PolicyScope::class); }
}
