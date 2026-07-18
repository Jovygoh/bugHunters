<?php

namespace App\Application\Notifications\Services;

use App\Domain\Notifications\Repositories\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
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
                    'status' => 'pending',
                ]);
            }

            return $notification;
        });
    }

    public function markRead(string $organizationId, string $notificationId, string $userId): Model
    {
        $notification = $this->notifications->findOrFail($notificationId);
        abort_unless(
            $notification->getAttribute('organization_id') === $organizationId
            && $notification->getAttribute('recipient_user_id') === $userId,
            404
        );

        return $this->notifications->update($notification, ['read_at' => now()]);
    }
}

