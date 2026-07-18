<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends UuidModel
{
    use SoftDeletes;

    protected $table = 'notifications';

    protected $fillable = [
            'organization_id',
            'recipient_user_id',
            'notification_type',
            'severity',
            'title',
            'body',
            'data',
            'incident_id',
            'policy_evaluation_id',
            'read_at',
            'expires_at',
        ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function recipientUser(): BelongsTo { return $this->belongsTo(User::class, 'recipient_user_id'); }

    public function incident(): BelongsTo { return $this->belongsTo(Incident::class, 'incident_id'); }

    public function policyEvaluation(): BelongsTo { return $this->belongsTo(PolicyEvaluation::class, 'policy_evaluation_id'); }

    public function deliveries(): HasMany { return $this->hasMany(NotificationDelivery::class); }
}

