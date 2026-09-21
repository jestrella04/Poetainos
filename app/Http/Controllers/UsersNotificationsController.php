<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class UsersNotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response|LengthAwarePaginator<int, DatabaseNotification>
     */
    public function index(): Response|LengthAwarePaginator
    {
        $user = $this->requireAuthUser();

        $tab = in_array(request('tab'), ['unread', 'all'], true) ? request('tab') : 'unread';

        $notifications = ($tab === 'unread' ? $user->unreadNotifications() : $user->notifications())
            ->paginate($this->pagination)
            ->withQueryString();

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

        $notifications->each(function (DatabaseNotification $notification) use ($notifierUsers, $notifierWritings): void {
            $notification['notifier_user'] = $notifierUsers->get($notification->data['user_id'] ?? null);
            $notification['notifier_writing'] = $notifierWritings->get($notification->data['writing_id'] ?? null);
        });

        if (request()->expectsJson()) {
            return $notifications;
        }

        return Inertia::render('notifications/PoNotificationsIndex', [
            'meta' => [
                'title' => getPageTitle([__('Notifications')]),
            ],
            'tab' => $tab,
            'notifications' => Inertia::optional(fn () => $notifications),
        ]);
    }

    public function clear(): RedirectResponse
    {
        $this->requireAuthUser()->unreadNotifications->markAsRead();

        return to_route('notifications.index');
    }

    public function show(string $notificationId): RedirectResponse
    {
        $notification = $this->requireAuthUser()->notifications()->findOrFail($notificationId);

        $notification->markAsRead();

        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        return redirect(route('writings.show', Writing::findOrFail($notification->data['writing_id'] ?? null)));
    }

    public function email(string $enable): JsonResponse
    {
        $this->requireAuthUser()->setEmailNotifications(isTruthy($enable));

        return response()->json(null, 204);
    }
}
