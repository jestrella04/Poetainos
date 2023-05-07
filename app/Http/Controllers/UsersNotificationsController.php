<?php

namespace App\Http\Controllers;

use App\Models\Writing;

use function PHPUnit\Framework\isNull;

class UsersNotificationsController extends Controller
{
    public function listUnread() {
        $user = auth()->user();
        $notifications = \App\Models\User::find($user->id)->unreadNotifications()->paginate($this->pagination);
        return $this->list($notifications);
    }

    public function listAll() {
        $user = auth()->user();
        $notifications = \App\Models\User::find($user->id)->notifications()->paginate($this->pagination);
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

    public function email($enable)
    {
        $user = auth()->user();
        $user->emailNotifications($enable);

        return response()->json(null, 204);
    }

    public function status()
    {
        $info = auth()->user()->extra_info;
        $status = [];

        if (!is_null($info['notifications'])) {
            $status = $info['notifications'];
        } else {
            $status['email'] = 'on';
        }

        if (empty($status['email'])) {
            $status['email'] = 'on';
        }

        return $status;
    }
}
