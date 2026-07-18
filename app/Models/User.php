<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'users';

    protected $fillable = [
            'organization_id',
            'name',
            'email',
            'normalized_email',
            'password',
            'status',
            'email_verified_at',
            'last_login_at',
            'last_login_ip',
            'password_changed_at',
            'must_change_password',
            'settings',
        ];

    protected $hidden = [
            'password',
            'remember_token',
        ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
            'settings' => 'array',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function employee(): HasOne { return $this->hasOne(Employee::class); }

    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class, 'user_roles')->withPivot(['id', 'organization_id', 'assigned_by', 'expires_at'])->withTimestamps(); }
}
