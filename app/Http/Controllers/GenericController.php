<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Pagination\Paginator;
use Inertia\Inertia;
use Inertia\Response;

class GenericController extends Controller
{
    /**
     * @return Response|Paginator<int, Writing>
     */
    public function writings(User $user): Response|Paginator
    {
        $sort = resolveSort(['latest', 'popular', 'likes']);
        $writings = $user->writings()
            ->visibleTo($this->getBlockedUsers())
            ->withListingRelations()
            ->sorted($sort);

        if (request()->expectsJson()) {
            return $writings->simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => getPageTitle([__('Writings'), $user->getName()]),
                'canonical' => route('home'),
            ],
            'writings' => Inertia::optional(fn () => $writings->simplePaginate($this->pagination)->withQueryString()),
            'sort' => $sort,
        ]);
    }

    /**
     * @return Response|Paginator<int, Writing>
     */
    public function shelf(User $user): Response|Paginator
    {
        $sort = resolveSort(['latest', 'popular', 'likes']);
        $writings = Writing::whereIn('id', $user->shelf()->pluck('id'))
            ->visibleTo($this->getBlockedUsers())
            ->withListingRelations()
            ->sorted($sort);

        if (request()->expectsJson()) {
            return $writings->simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => getPageTitle([__('Shelf'), $user->getName()]),
                'canonical' => route('home'),
            ],
            'writings' => Inertia::optional(fn () => $writings->simplePaginate($this->pagination)->withQueryString()),
            'sort' => $sort,
        ]);
    }

    /**
     * @return Response|Paginator<int, Writing>
     */
    public function likes(User $user): Response|Paginator
    {
        $sort = resolveSort(['latest', 'popular', 'likes']);
        $writings = Writing::whereIn('id', $user->likes()->where('likeable_type', Writing::class)->pluck('likeable_id'))
            ->visibleTo($this->getBlockedUsers())
            ->whereNot('user_id', $user->id)
            ->withListingRelations()
            ->sorted($sort);

        if (request()->expectsJson()) {
            return $writings->simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => getPageTitle([__('Likes'), $user->getName()]),
                'canonical' => route('home'),
            ],
            'writings' => Inertia::optional(fn () => $writings->simplePaginate($this->pagination)->withQueryString()),
            'sort' => $sort,
        ]);
    }

    public function explore(): Response
    {
        return Inertia::render('generic/PoExploreIndex', [
            'meta' => [
                'title' => getPageTitle([__('Explore')]),
            ],
            'totals' => [
                'writings' => Writing::count(),
                'authors' => User::has('writings')->count(),
            ],
            'categories' => [
                'main' => Category::withCount('writings')
                    ->whereNull('parent_id')
                    ->orderByDesc('writings_count')
                    ->having('writings_count', '>', 0)
                    ->get(),
                'alt' => Category::withCount('writings')
                    ->whereNotNull('parent_id')
                    ->orderByDesc('writings_count')
                    ->having('writings_count', '>', 0)
                    ->get(),
            ],
            'tags' => Tag::withCount('writings')
                ->orderByDesc('writings_count')
                ->having('writings_count', '>', 0)
                ->take(20)
                ->get(),
            'authors' => User::select(
                'id',
                'username',
                'name',
                'karma',
                'extra_info->avatar AS avatar',
            )->orderByRaw('(CASE WHEN `karma` IS NULL THEN \'F\' ELSE `karma` END) ASC')
                ->orderBy('aura', 'desc')
                ->take(20)
                ->get(),
        ]);
    }

    public function manifest(): \stdClass
    {
        $json = json_decode((string) file_get_contents(base_path('resources/json/manifest.json')));

        $json->name = getSiteConfig('name');
        $json->gcm_sender_id = config('webpush.gcm.sender_id');
        $json->short_name = getSiteConfig('name');
        $json->description = getSiteConfig('slogan');

        $shortcuts = [
            'account' => [__('My account'), route('users.account')],
            'publish' => [__('Publish'), route('writings.create')],
            'featured' => [__('Golden Flowers'), route('writings.awards')],
            'random' => [__('Random'), route('writings.random')],
            'authors' => [__('Writers'), route('users.index')],
        ];

        foreach ($json->shortcuts as $shortcut) {
            if (! isset($shortcuts[$shortcut->name])) {
                continue;
            }

            [$label, $url] = $shortcuts[$shortcut->name];
            $shortcut->name = $label;
            $shortcut->short_name = $label;
            $shortcut->url = $url;
        }

        foreach ($json->related_applications as $app) {
            if ($app->platform === 'webapp') {
                $app->url = route('pwa.manifest');
            } elseif ($app->platform === 'play') {
                $app->url = config('services.google.play_store.url');
                $app->id = config('services.google.play_store.id');
            }
        }

        $json->iarc_rating_id = config('services.compliance.iarc_rating_id');

        return $json;
    }

    public function offline(): Response
    {
        return Inertia::render('generic/PoOffline', [
            'meta' => [],
        ]);
    }
}
