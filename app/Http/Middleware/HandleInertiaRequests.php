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
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $ziggy = new Ziggy($group = null, $request->url());
        $user = auth()->check() ? User::find(auth()->user()->id) : null;

        return array_merge(parent::share($request), [
            'ziggy' => $ziggy->toArray(),
            'auth' => [
                'user' => auth()->check()
                    ? User::select('id', 'username', 'name', 'extra_info->avatar AS avatar')->where('id', $user->id)->firstOrFail()
                    : null,
                'admin' => auth()->check() ? $user->isAllowed('admin') : null,
                'notifications' => auth()->check() ? $user->unreadNotifications->count() : 0,
                'liked' => [
                    'writings' => auth()->check() ? Writing::whereIn('id', $user->likes()->where('likeable_type', Writing::class)->pluck('likeable_id'))->pluck('id') : [],
                    'comments' => auth()->check() ? Comment::whereIn('id', $user->likes()->where('likeable_type', Comment::class)->pluck('likeable_id'))->pluck('id') : [],
                ],
                'shelved' => auth()->check() ? $user->shelf()->pluck('id') : [],
            ],
            'route' => [
                'name' => $request->route()->getName()
            ],
            'site' => [
                'name' => getSiteConfig('name'),
                'slogan' => getSiteConfig('slogan'),
                'pagination' => getSiteConfig('pagination'),
                'social' => getSiteConfig('social'),
                'stores' => getSiteConfig('stores'),
            ],
            'flash' => [
                'message' => $request->session()->get('message')
            ],
        ]);
    }
}
