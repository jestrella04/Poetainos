<?php

namespace App\Http\Middleware;

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default. Anything that costs a
     * query is a closure, so only Inertia responses that use it pay for it.
     */
    public function share(Request $request): array
    {
        $user = auth()->check()
            ? User::forAuthorSummary()->find(auth()->id())
            : null;

        return array_merge(parent::share($request), [
            'ziggy' => Inertia::once(fn (): array => (new Ziggy)->toArray()),
            'auth' => [
                'user' => $user,
                'admin' => $request->user()?->isAllowed('admin'),
                'notifications' => fn (): int => $user?->unreadNotifications()->count() ?? 0,
                'liked' => [
                    'writings' => fn (): Collection|array => $this->likedIds($user, Writing::class),
                    'comments' => fn (): Collection|array => $this->likedIds($user, Comment::class),
                ],
                'shelved' => fn (): Collection|array => $user?->shelf()->pluck('id') ?? [],
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

    /**
     * The ids of the given kind of content the user has liked.
     *
     * @param  class-string<Model>  $likeableType
     * @return Collection<int, int>|array<never, never>
     */
    private function likedIds(?User $user, string $likeableType): Collection|array
    {
        return $user?->likes()->where('likeable_type', $likeableType)->pluck('likeable_id') ?? [];
    }
}
