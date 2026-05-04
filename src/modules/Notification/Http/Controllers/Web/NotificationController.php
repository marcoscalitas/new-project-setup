<?php

namespace Modules\Notification\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController
{
    public function redirect(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        $url = $notification->data['url'] ?? route('home');

        return redirect($url);
    }
}
