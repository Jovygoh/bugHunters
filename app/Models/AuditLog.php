<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends UuidModel
{
    protected $table = 'audit_logs';

    protected $fillable = [
            'organization_id',
            'actor_user_id',
            'actor_type',
            'actor_identifier',
            'action',
            'module',
            'auditable_type',
            'auditable_id',
            'outcome',
            'description',
            'old_values',
            'new_values',
            'changed_fields',
            'request_id',
            'correlation_id',
            'source',
            'http_method',
            'request_path',
            'ip_address',
            'user_agent',
            'metadata',
            'previous_hash',
            'entry_hash',
            'occurred_at',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'changed_fields' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function actorUser(): BelongsTo { return $this->belongsTo(User::class, 'actor_user_id'); }

    public function auditable(): MorphTo { return $this->morphTo(); }
}
