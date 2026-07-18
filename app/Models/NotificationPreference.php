<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationPreference extends UuidModel
{
    protected $table = 'notification_preferences';

    protected $fillable = [
            'organization_id',
            'user_id',
            'notification_type',
            'channel',
            'is_enabled',
            'minimum_severity',
            'quiet_hours',
        ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'quiet_hours' => 'array',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}

