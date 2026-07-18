<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OutboxMessage extends UuidModel
{
    protected $table = 'outbox_messages';

    protected $fillable = [
            'organization_id',
            'aggregate_type',
            'aggregate_id',
            'event_type',
            'payload',
            'headers',
            'occurred_at',
            'available_at',
            'processed_at',
            'attempt_count',
            'last_error',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'occurred_at' => 'datetime',
            'available_at' => 'datetime',
            'processed_at' => 'datetime',
            'attempt_count' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }
}

