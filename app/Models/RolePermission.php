<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RolePermission extends UuidModel
{
    protected $table = 'role_permissions';

    protected $fillable = [
            'role_id',
            'permission_id',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo { return $this->belongsTo(Role::class, 'role_id'); }

    public function permission(): BelongsTo { return $this->belongsTo(Permission::class, 'permission_id'); }
}

