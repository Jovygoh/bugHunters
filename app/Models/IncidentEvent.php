<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IncidentEvent extends UuidModel
{
    protected $table = 'incident_events';

    protected $fillable = [
            'organization_id',
            'incident_id',
            'actor_user_id',
            'event_type',
            'from_status',
            'to_status',
            'from_severity',
            'to_severity',
            'payload',
            'occurred_at',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function incident(): BelongsTo { return $this->belongsTo(Incident::class, 'incident_id'); }

    public function actorUser(): BelongsTo { return $this->belongsTo(User::class, 'actor_user_id'); }
}

