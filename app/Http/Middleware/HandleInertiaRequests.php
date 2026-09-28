<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SocialAuthController;
use App\Models\User;
use App\Models\UserProfile;
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
        $routeGroup = ziggyRouteGroup();

        return array_merge(parent::share($request), [
            // Keyed by group, so signing in or out as an admin sends the other route table
            'ziggy' => Inertia::once(fn (): array => (new Ziggy($routeGroup))->toArray())->as('ziggy-'.$routeGroup),
            'auth' => [
                'user' => fn (): ?array => $this->authUserSummary($request->user()),
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
                'socialNetworks' => UserProfile::SOCIAL_NETWORKS,
                'authProviders' => SocialAuthController::PROVIDERS,
            ],
        ]);
    }

    /**
     * Who is signed in, in the author summary shape listings use, from the
     * already loaded user.
     *
     * @return array{id: int, username: string, name: string|null, avatar_url: string|null}|null
     */
    private function authUserSummary(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'avatar_url' => $user->profile->avatar_url,
        ];
    }
}
