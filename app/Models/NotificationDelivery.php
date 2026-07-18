<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationDelivery extends UuidModel
{
    protected $table = 'notification_deliveries';

    protected $fillable = [
            'organization_id',
            'notification_id',
            'channel',
            'destination_hash',
            'status',
            'provider_message_id',
            'attempt_count',
            'last_attempt_at',
            'delivered_at',
            'failed_at',
            'failure_code',
            'failure_message',
        ];

    protected function casts(): array
    {
        return [
            'attempt_count' => 'integer',
            'last_attempt_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function notification(): BelongsTo { return $this->belongsTo(Notification::class, 'notification_id'); }
}

