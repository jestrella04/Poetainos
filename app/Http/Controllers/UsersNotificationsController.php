<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UsersNotificationsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $filter = request('filter') ?? 'unread';

        if ('all' === $filter) {
            $notifications = $user->notifications;
        } else {
            $notifications = $user->unreadNotifications;
        }

        $params = [
            'title' => __('Notifications') . ' - ' . $user->getName(),
        ];

        return view('notifications.index', [
            'user' => $user,
            'notifications' => $notifications,
            'params' => $params,
        ]);
    }

    public function markAllRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return redirect(route('notifications.index'));
    }
}
