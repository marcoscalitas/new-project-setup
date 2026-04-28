<?php

namespace Modules\Notification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Http\Resources\NotificationResource;
use Modules\Notification\Services\NotificationService;

class NotificationController
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        if (request()->expectsJson()) {
            $notifications = $this->notificationService->getAll($request->user(), $perPage);
            return NotificationResource::collection($notifications)->response();
        }

        $notifications = $this->notificationService->getAll($request->user(), null);
        return view('notification::notifications.index', compact('notifications'));
    }

    public function unread(Request $request): JsonResponse|\Illuminate\View\View
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $notifications = $this->notificationService->getUnread($request->user(), $perPage);

        if (request()->expectsJson()) {
            return NotificationResource::collection($notifications)->response();
        }

        return view('notification::notifications.index', compact('notifications'));
    }

    public function show(Request $request, string $id): JsonResponse|\Illuminate\View\View
    {
        $notification = $this->notificationService->findById($request->user(), $id);

        if (request()->expectsJson()) {
            return response()->json(new NotificationResource($notification));
        }

        return view('notification::notifications.show', compact('notification'));
    }

    public function markAsRead(Request $request, string $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->notificationService->markAsRead($request->user(), $id);

        if (request()->expectsJson()) {
            return response()->json(['message' => __('notifications.marked_as_read')]);
        }

        return redirect()->route('notifications.index')->with('success', __('notifications.marked_as_read'));
    }

    public function markAllAsRead(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        if (request()->expectsJson()) {
            return response()->json(['message' => __('notifications.all_marked_as_read')]);
        }

        return redirect()->route('notifications.index')->with('success', __('notifications.all_marked_as_read'));
    }

    public function destroy(Request $request, string $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->notificationService->delete($request->user(), $id);

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('notifications.index')->with('success', __('notifications.deleted'));
    }
}
