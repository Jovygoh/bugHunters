<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserRole extends UuidModel
{
    protected $table = 'user_roles';

    protected $fillable = [
            'organization_id',
            'user_id',
            'role_id',
            'assigned_by',
            'expires_at',
        ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }

    public function role(): BelongsTo { return $this->belongsTo(Role::class, 'role_id'); }
}

