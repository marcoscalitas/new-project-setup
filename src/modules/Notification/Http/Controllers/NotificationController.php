<?php

namespace Modules\Notification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Http\Resources\NotificationResource;
use Modules\Notification\Services\NotificationService;

class NotificationController
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getAll($request->user());

        return response()->json(NotificationResource::collection($notifications));
    }

    public function unread(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getUnread($request->user());

        return response()->json(NotificationResource::collection($notifications));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $notification = $this->notificationService->findById($request->user(), $id);

        return response()->json(new NotificationResource($notification));
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $this->notificationService->markAsRead($request->user(), $id);

        return response()->json(['message' => 'Notificação marcada como lida.']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json(['message' => 'Todas as notificações marcadas como lidas.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->notificationService->delete($request->user(), $id);

        return response()->json(null, 204);
    }
}
