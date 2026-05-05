<?php

namespace Modules\Notification\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter') === 'unread' ? 'unread' : 'all';
        $query = $filter === 'unread'
            ? Auth::user()->unreadNotifications()
            : Auth::user()->notifications();

        $notifications = $query->latest()->paginate(15)->withQueryString();
        $unreadCount = Auth::user()->unreadNotifications()->count();

        return view('notification::notifications.index', compact('notifications', 'filter', 'unreadCount'));
    }

    public function redirect(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        $url = data_get($notification->data, 'data.url')
            ?? data_get($notification->data, 'url')
            ?? route('home');

        return redirect($url);
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }
}
