<?php

namespace App\Application\Notifications\Services;

use App\Domain\Notifications\Repositories\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/** Persists user notifications and creates one delivery record per requested channel. */
final readonly class NotificationService
{
    public function __construct(private NotificationRepositoryInterface $notifications) {}

    public function send(string $organizationId, string $userId, array $message, array $channels): Model
    {
        return DB::transaction(function () use ($organizationId, $userId, $message, $channels): Model {
            $notification = $this->notifications->create($message + [
                'organization_id' => $organizationId,
                'recipient_user_id' => $userId,
            ]);

            foreach (array_unique($channels) as $channel) {
                $this->notifications->createDelivery([
                    'organization_id' => $organizationId,
                    'notification_id' => $notification->getKey(),
                    'channel' => $channel,
                    'status' => $channel === 'database' ? 'delivered' : 'pending',
                    'delivered_at' => $channel === 'database' ? now() : null,
                ]);
            }

            return $notification;
        });
    }

    public function markRead(string $organizationId, string $notificationId, string $userId): Model
    {
        $notification = $this->notifications->findForUser($organizationId, $userId, $notificationId)
            ?? throw (new ModelNotFoundException)->setModel('Notification', [$notificationId]);

        return $this->notifications->update($notification, ['read_at' => now()]);
    }

    public function list(string $organizationId, string $userId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->notifications->paginateForUser($organizationId, $userId, $filters, $perPage);
    }

    public function unreadCount(string $organizationId, string $userId): int
    {
        return $this->notifications->unreadCount($organizationId, $userId);
    }

    public function updatePreference(string $organizationId, string $userId, array $attributes): Model
    {
        return $this->notifications->upsertPreference([
            'user_id' => $userId,
            'notification_type' => $attributes['notification_type'],
            'channel' => $attributes['channel'],
        ], $attributes + [
            'organization_id' => $organizationId,
            'user_id' => $userId,
        ]);
    }
}
