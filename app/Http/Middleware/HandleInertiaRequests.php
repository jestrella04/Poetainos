<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
     * Defines the props that are shared by default. Anything that costs a
     * query is a closure, so only Inertia responses that use it pay for it.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'ziggy' => Inertia::once(fn (): array => (new Ziggy)->toArray()),
            'auth' => [
                'user' => fn (): ?User => $request->user() === null
                    ? null
                    : User::forAuthorSummary()->find($request->user()->id),
                'admin' => fn (): bool => $request->user()?->isAllowed('admin') === true,
                'notifications' => fn (): int => $request->user()?->unreadNotifications()->count() ?? 0,
            ],
            'route' => [
                'name' => $request->route()?->getName(),
            ],
            'site' => [
                'name' => getSiteConfig('name'),
                'slogan' => getSiteConfig('slogan'),
                'image' => asset('images/card.png'),
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
