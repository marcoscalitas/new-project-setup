<?php

namespace Modules\User\Actions;

use Modules\User\Models\User;

class DeleteUser
{
    public function execute(User $user): void
    {
        $user->delete();
    }
}
