<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLogExport extends UuidModel
{
    protected $table = 'audit_log_exports';

    protected $fillable = [
            'organization_id',
            'requested_by',
            'status',
            'format',
            'filters',
            'storage_disk',
            'storage_path',
            'sha256',
            'record_count',
            'started_at',
            'completed_at',
            'expires_at',
            'failure_message',
        ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'record_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }
}

