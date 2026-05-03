<?php

namespace Modules\Notification\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Http\Resources\NotificationResource;
use Modules\Notification\Services\NotificationService;

class NotificationController
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage       = min((int) $request->query('per_page', 15), 100);
        $notifications = $this->notificationService->getAll($request->user(), $perPage);

        return NotificationResource::collection($notifications)->response();
    }

    public function unread(Request $request): JsonResponse
    {
        $perPage       = min((int) $request->query('per_page', 15), 100);
        $notifications = $this->notificationService->getUnread($request->user(), $perPage);

        return NotificationResource::collection($notifications)->response();
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $notification = $this->notificationService->findById($request->user(), $id);

        return response()->json(new NotificationResource($notification));
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $this->notificationService->markAsRead($request->user(), $id);

        return response()->json(['message' => __('notifications.marked_as_read')]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json(['message' => __('notifications.all_marked_as_read')]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->notificationService->delete($request->user(), $id);

        return response()->json(null, 204);
    }
}
