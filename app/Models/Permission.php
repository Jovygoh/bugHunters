<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Permission extends UuidModel
{
    protected $table = 'permissions';

    protected $fillable = [
            'code',
            'name',
            'description',
            'module',
        ];

    protected function casts(): array
    {
        return [];
    }

    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class, 'role_permissions')->withPivot(['id', 'created_at']); }
}
