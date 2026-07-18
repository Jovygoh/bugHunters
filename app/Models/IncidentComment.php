<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IncidentComment extends UuidModel
{
    use SoftDeletes;

    protected $table = 'incident_comments';

    protected $fillable = [
            'organization_id',
            'incident_id',
            'author_user_id',
            'body',
            'visibility',
            'edited_at',
        ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function incident(): BelongsTo { return $this->belongsTo(Incident::class, 'incident_id'); }

    public function authorUser(): BelongsTo { return $this->belongsTo(User::class, 'author_user_id'); }
}

