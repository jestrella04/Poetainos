<?php

namespace App\Http\Middleware;

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        $ziggy = new Ziggy;
        $user = auth()->check()
            ? User::forAuthorSummary()->find(auth()->id())
            : null;

        return array_merge(parent::share($request), [
            'ziggy' => $ziggy->toArray(),
            'auth' => [
                'user' => $user,
                'admin' => $request->user()?->isAllowed('admin'),
                'notifications' => $user?->unreadNotifications->count() ?? 0,
                'liked' => [
                    'writings' => $user !== null ? Writing::whereIn('id', $user->likes()->where('likeable_type', Writing::class)->pluck('likeable_id'))->pluck('id') : [],
                    'comments' => $user !== null ? Comment::whereIn('id', $user->likes()->where('likeable_type', Comment::class)->pluck('likeable_id'))->pluck('id') : [],
                ],
                'shelved' => $user?->shelf()->pluck('id') ?? [],
            ],
            'route' => [
                'name' => $request->route()?->getName(),
            ],
            'site' => [
                'name' => getSiteConfig('name'),
                'slogan' => getSiteConfig('slogan'),
                'pagination' => getSiteConfig('pagination'),
                'social' => getSiteConfig('social'),
                'stores' => getSiteConfig('stores'),
            ],
            'flash' => [
                'message' => $request->session()->get('message'),
            ],
        ]);
    }
}
