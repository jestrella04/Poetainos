<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Writing;
use Inertia\Inertia;
use Inertia\Response;

class UsersNotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $user = auth()->user();
        $tab = in_array(request('tab'), ['unread', 'all']) ? request('tab') : 'unread';
        $notifications = [];

        if ($tab === 'unread') {
            $notifications = User::find($user->id)->unreadNotifications()->paginate($this->pagination)->withQueryString();
        } else {
            $notifications = User::find($user->id)->notifications()->paginate($this->pagination)->withQueryString();
        }

        $notifierUserIds = $notifications->pluck('data.user_id')->filter()->unique();
        $notifierWritingIds = $notifications->pluck('data.writing_id')->filter()->unique();

        $notifierUsers = User::forAuthorSummary()
            ->whereIn('id', $notifierUserIds)
            ->get()
            ->keyBy('id');

        $notifierWritings = Writing::select('id', 'title', 'slug')
            ->whereIn('id', $notifierWritingIds)
            ->get()
            ->keyBy('id');

        $notifications->each(function ($notification) use ($notifierUsers, $notifierWritings): void {
            $notification['notifier_user'] =
                isset($notification->data['user_id'])
                ? $notifierUsers->get($notification->data['user_id'])
                : null;

            $notification['notifier_writing'] =
                isset($notification->data['writing_id'])
                ? $notifierWritings->get($notification->data['writing_id'])
                : null;
        });

        if (request()->expectsJson()) {
            return $notifications;
        }

        return Inertia::render('notifications/PoNotificationsIndex', [
            'meta' => [],
            'tab' => $tab,
            'notifications' => Inertia::optional(fn () => $notifications),
        ]);
    }

    public function clear()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect(route('notifications.index'));
    }

    public function show($notificationId)
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
        auth()->user()->emailNotifications($enable);

        return response()->json(null, 204);
    }

    public function status()
    {
        $info = auth()->user()->extra_info;
        $status = [];

        if (array_key_exists('notifications', $info)) {
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
