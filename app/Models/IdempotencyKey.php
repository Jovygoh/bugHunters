<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IdempotencyKey extends UuidModel
{
    protected $table = 'idempotency_keys';

    protected $fillable = [
            'organization_id',
            'user_id',
            'idempotency_key',
            'request_method',
            'request_path',
            'request_hash',
            'status',
            'response_status',
            'response_body',
            'locked_until',
            'expires_at',
        ];

    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
            'locked_until' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}

