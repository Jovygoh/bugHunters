<?php

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Application\Notifications\Services\NotificationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notifications\IndexNotificationRequest;
use App\Http\Requests\Api\V1\Notifications\ReadNotificationRequest;
use App\Http\Requests\Api\V1\Notifications\UpdateNotificationPreferenceRequest;
use App\Http\Resources\Api\V1\NotificationPreferenceResource;
use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(IndexNotificationRequest $request): AnonymousResourceCollection
    {
        return NotificationResource::collection($this->notifications->list(
            $request->user()->organization_id,
            $request->user()->getKey(),
            $request->filters(),
            $request->integer('per_page', 25),
        ));
    }

    public function unreadCount(IndexNotificationRequest $request): JsonResponse
    {
        return response()->json(['data' => [
            'count' => $this->notifications->unreadCount(
                $request->user()->organization_id,
                $request->user()->getKey(),
            ),
        ]]);
    }

    public function markRead(ReadNotificationRequest $request, string $notification): NotificationResource
    {
        return new NotificationResource($this->notifications->markRead(
            $request->user()->organization_id,
            $notification,
            $request->user()->getKey(),
        ));
    }

    public function updatePreference(UpdateNotificationPreferenceRequest $request): NotificationPreferenceResource
    {
        return new NotificationPreferenceResource($this->notifications->updatePreference(
            $request->user()->organization_id,
            $request->user()->getKey(),
            $request->validated(),
        ));
    }
}

