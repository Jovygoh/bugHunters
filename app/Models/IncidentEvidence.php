<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IncidentEvidence extends UuidModel
{
    use SoftDeletes;

    protected $table = 'incident_evidence';

    protected $fillable = [
            'organization_id',
            'incident_id',
            'uploaded_by',
            'evidence_type',
            'name',
            'description',
            'storage_disk',
            'storage_path',
            'mime_type',
            'size_bytes',
            'sha256',
            'encryption_key_ref',
            'captured_at',
        ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'captured_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function incident(): BelongsTo { return $this->belongsTo(Incident::class, 'incident_id'); }
}

