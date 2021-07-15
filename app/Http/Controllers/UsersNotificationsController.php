<?php

namespace App\Http\Controllers;

use App\User;
use App\Writing;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

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
            'title' => getPageTitle([
                __('My notifications'),
                ]),
        ];

        return view('notifications.index', [
            'user' => $user,
            'notifications' => $notifications,
            'params' => $params,
        ]);
    }

    public function clear()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return redirect(route('notifications.index'));
    }

    public function read($notificationId)
    {
        $notification = auth()->user()->notifications->find($notificationId);

        if($notification) {
            $notification->markAsRead();
            return redirect(route('writings.show', Writing::findOrFail($notification->data['writing_id'])));
        }

        return abort(401);
    }
}
