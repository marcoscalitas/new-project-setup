<?php

namespace Modules\Notification\Services;

use Illuminate\Support\Collection;
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
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function delete(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->delete();
    }
}
