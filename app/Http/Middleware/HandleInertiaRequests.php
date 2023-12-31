<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

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
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => auth()->check()
                    ? User::select('username', 'name', 'extra_info->avatar AS avatar')->where('id', auth()->user()->id)->firstOrFail()
                    : null,
                'admin' => auth()->check() ? User::find(auth()->user()->id)->isAllowed('admin') : null,
                'notifications' => auth()->check() ? auth()->user()->unreadNotifications->count() : 0,
            ],
            'route' => [
                'name' => $request->route()->getName()
            ],
            'site' => [
                'name' => getSiteConfig('name'),
                'slogan' => getSiteConfig('slogan'),
                'pagination' => getSiteConfig('pagination'),
                //'social' => getSiteConfig('social'),
                //'stores' => getSiteConfig('stores'),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message')
            ],
        ]);
    }
}
