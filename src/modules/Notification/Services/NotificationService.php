<?php

namespace Modules\Notification\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Notification\Events\NotificationDeleted;
use Modules\Notification\Events\NotificationRead;
use Modules\User\Models\User;

class NotificationService
{
    public function getAll(User $user, ?int $perPage = 15)
    {
        $query = $user->notifications();
        return $perPage === null ? $query->get() : $query->paginate($perPage);
    }

    public function getUnread(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->unreadNotifications()->paginate($perPage);
    }

    public function findById(User $user, string $id)
    {
        return $user->notifications()->findOrFail($id);
    }

    public function markAsRead(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->markAsRead();

        NotificationRead::dispatch($user->ulid, $id);
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function delete(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->delete();

        NotificationDeleted::dispatch($user->ulid, $id);
    }
}
