<?php

namespace Modules\Notification\Services;

use Illuminate\Support\Collection;
use Modules\Notification\Actions\DeleteNotification;
use Modules\Notification\Actions\MarkAllNotificationsAsRead;
use Modules\Notification\Actions\MarkNotificationAsRead;
use Modules\Notification\Events\NotificationDeleted;
use Modules\Notification\Events\NotificationRead;
use Modules\User\Models\User;

class NotificationService
{
    public function __construct(
        private MarkNotificationAsRead $markAsReadAction,
        private MarkAllNotificationsAsRead $markAllAsReadAction,
        private DeleteNotification $deleteAction,
    ) {}

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
        $this->markAsReadAction->execute($user, $id);

        NotificationRead::dispatch($user->id, $id);
    }

    public function markAllAsRead(User $user): void
    {
        $this->markAllAsReadAction->execute($user);
    }

    public function delete(User $user, string $id): void
    {
        $userId = $user->id;

        $this->deleteAction->execute($user, $id);

        NotificationDeleted::dispatch($userId, $id);
    }
}
