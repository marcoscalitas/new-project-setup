<?php

namespace Modules\Notification\Services;

use Illuminate\Support\Collection;
use Modules\Notification\Events\NotificationDeleted;
use Modules\Notification\Events\NotificationRead;
use Modules\User\Models\User;

class NotificationService
{
    public function getAll(User $user): Collection
    {
        return $user->notifications;
    }

    public function getUnread(User $user): Collection
    {
        return $user->unreadNotifications;
    }

    public function findById(User $user, string $id)
    {
        return $user->notifications()->findOrFail($id);
    }

    public function markAsRead(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->markAsRead();

        NotificationRead::dispatch($user->id, $id);
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function delete(User $user, string $id): void
    {
        $userId = $user->id;

        $user->notifications()->findOrFail($id)->delete();

        NotificationDeleted::dispatch($userId, $id);
    }
}
