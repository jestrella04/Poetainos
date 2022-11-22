<?php

namespace App\Http\Controllers;

use App\Writing;

class UsersNotificationsController extends Controller
{
    public function listUnread() {
        $user = auth()->user();
        $notifications = \App\User::find($user->id)->unreadNotifications()->paginate($this->pagination);
        return $this->list($notifications);
    }

    public function listAll() {
        $user = auth()->user();
        $notifications = \App\User::find($user->id)->notifications()->paginate($this->pagination);
        return $this->list($notifications);
    }

    private function list($notifications)
    {
        $params = [
            'title' => getPageTitle([
                __('My notifications'),
                ]),
        ];

        return view('notifications.index', [
            'user' => auth()->user(),
            'notifications' => $notifications,
            'params' => $params,
        ]);
    }

    public function clear()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return redirect(route('notifications.list.unread'));
    }

    public function read($notificationId)
    {
        $notification = auth()->user()->notifications->find($notificationId);

        if ($notification) {
            $notification->markAsRead();

            if (isset($notification->data['url'])) {
                $redirectUrl = redirect($notification->data['url']);
            } else {
                $redirectUrl = redirect(route('writings.show', Writing::findOrFail($notification->data['writing_id'])));
            }

            return $redirectUrl;
        }

        return abort(401);
    }
}
