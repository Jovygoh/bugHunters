<?php

namespace App\Domain\Notifications\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/** @extends RepositoryInterface<Notification> */
interface NotificationRepositoryInterface extends RepositoryInterface
{
    public function unreadForUser(string $organizationId, string $userId, int $perPage = 25): LengthAwarePaginator;

    public function preferencesForUser(string $userId): Collection;

    public function upsertPreference(array $identity, array $attributes): NotificationPreference;

    public function createDelivery(array $attributes): NotificationDelivery;
}

