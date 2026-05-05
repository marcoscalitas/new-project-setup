<?php

namespace Modules\Notification\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Notification\Events\NotificationDeleted;
use Modules\Notification\Events\NotificationRead;
use Shared\Contracts\Notification\NotificationRecipient;

class NotificationService
{
    public function getAll(NotificationRecipient $recipient, ?int $perPage = 15)
    {
        $query = $recipient->notifications();
        return $perPage === null ? $query->get() : $query->paginate($perPage);
    }

    public function getUnread(NotificationRecipient $recipient, int $perPage = 15): LengthAwarePaginator
    {
        return $recipient->unreadNotifications()->paginate($perPage);
    }

    public function findById(NotificationRecipient $recipient, string $id)
    {
        return $recipient->notifications()->findOrFail($id);
    }

    public function markAsRead(NotificationRecipient $recipient, string $id): void
    {
        $recipient->notifications()->findOrFail($id)->markAsRead();

        NotificationRead::dispatch($recipient->notificationRecipientId(), $id);
    }

    public function markAllAsRead(NotificationRecipient $recipient): void
    {
        $recipient->unreadNotifications->markAsRead();
    }

    public function delete(NotificationRecipient $recipient, string $id): void
    {
        $recipient->notifications()->findOrFail($id)->delete();

        NotificationDeleted::dispatch($recipient->notificationRecipientId(), $id);
    }
}
