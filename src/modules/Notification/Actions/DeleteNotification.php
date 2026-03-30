<?php

namespace Modules\Notification\Actions;

use Modules\User\Models\User;

class DeleteNotification
{
    public function execute(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->delete();
    }
}
