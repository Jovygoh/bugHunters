<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Notifications\Repositories\NotificationRepositoryInterface;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentNotificationRepository extends AbstractEloquentRepository implements NotificationRepositoryInterface
{
    public function __construct(Notification $model) { parent::__construct($model); }

    public function unreadForUser(string $organizationId, string $userId, int $perPage = 25): LengthAwarePaginator
    {
        return Notification::query()->where('organization_id', $organizationId)->where('recipient_user_id', $userId)->whereNull('read_at')->orderByDesc('created_at')->paginate($perPage);
    }

    public function preferencesForUser(string $userId): Collection
    {
        return NotificationPreference::query()->where('user_id', $userId)->get();
    }

    public function upsertPreference(array $identity, array $attributes): NotificationPreference
    {
        return NotificationPreference::query()->updateOrCreate($identity, $attributes);
    }

    public function createDelivery(array $attributes): NotificationDelivery
    {
        return NotificationDelivery::query()->create($attributes);
    }

    protected function filterable(): array { return ['organization_id', 'recipient_user_id', 'notification_type', 'severity', 'read_at']; }
}

