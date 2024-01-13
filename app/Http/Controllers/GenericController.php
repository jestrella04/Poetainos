<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use Inertia\Inertia;

class GenericController extends Controller
{
    public function writings(User $user)
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';
        $writings = $user->writings()->whereNotIn('user_id', $this->blockedUsers)
            ->withCount(['likes', 'comments', 'shelf'])
            ->with(['author' => function ($query) {
                $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
            }]);

        if ('latest' === $sort) {
            $writings = $writings->latest()->simplePaginate($this->pagination)->withQueryString();
        } elseif ('popular' === $sort) {
            $writings = $writings->orderBy('views', 'desc')->simplePaginate($this->pagination)->withQueryString();
        } elseif ('likes' === $sort) {
            $writings = $writings->orderBy('likes_count', 'desc')->simplePaginate($this->pagination)->withQueryString();
        }

        if (request()->expectsJson()) {
            return $writings;
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => getPageTitle([__('Writings'), $user->getName()]),
                'canonical' => route('home'),
            ],
            'writings' => $writings,
            'sort' => $sort,
        ]);
    }

    public function shelf(User $user)
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';
        $writings = Writing::whereIn('id', $user->shelf()->pluck('id'))
            ->whereNotIn('user_id', $this->blockedUsers)
            ->withCount(['likes', 'comments', 'shelf'])
            ->with(['author' => function ($query) {
                $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
            }]);

        if ('latest' === $sort) {
            $writings = $writings->latest()->simplePaginate($this->pagination)->withQueryString();
        } elseif ('popular' === $sort) {
            $writings = $writings->orderBy('views', 'desc')->simplePaginate($this->pagination)->withQueryString();
        } elseif ('likes' === $sort) {
            $writings = $writings->orderBy('likes_count', 'desc')->simplePaginate($this->pagination)->withQueryString();
        }

        if (request()->expectsJson()) {
            return $writings;
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => getPageTitle([__('Shelf'), $user->getName()]),
                'canonical' => route('home'),
            ],
            'writings' => $writings,
            'sort' => $sort,
        ]);
    }

    public function likes(User $user)
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';
        $writings = Writing::whereIn('id', $user->likes()->where('likeable_type', Writing::class)->pluck('likeable_id'))
            ->whereNotIn('user_id', $this->blockedUsers)
            ->whereNot('user_id', $user->id)
            ->withCount(['likes', 'comments', 'shelf'])
            ->with(['author' => function ($query) {
                $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
            }]);

        if ('latest' === $sort) {
            $writings = $writings->latest()->simplePaginate($this->pagination)->withQueryString();
        } elseif ('popular' === $sort) {
            $writings = $writings->orderBy('views', 'desc')->simplePaginate($this->pagination)->withQueryString();
        } elseif ('likes' === $sort) {
            $writings = $writings->orderBy('likes_count', 'desc')->simplePaginate($this->pagination)->withQueryString();
        }

        if (request()->expectsJson()) {
            return $writings;
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => getPageTitle([__('Likes'), $user->getName()]),
                'canonical' => route('home'),
            ],
            'writings' => $writings,
            'sort' => $sort,
        ]);
    }

    public function explore()
    {
        return Inertia::render('generic/PoExploreIndex', [
            'meta' => [
                'title' => getPageTitle([__('Explore')]),
            ],
            'categories' => [
                'main' => Category::withCount('writings')->whereNull('parent_id')->orderByDesc('writings_count')
                    ->having('writings_count', '>', 0)->get(),
                'alt' => Category::withCount('writings')->whereNotNull('parent_id')->orderByDesc('writings_count')
                    ->having('writings_count', '>', 0)->get(),
            ],
            'tags' => Tag::withCount('writings')->orderByDesc('writings_count')
                ->having('writings_count', '>', 0)->get()
                ->take(20),
            'authors' => User::select(
                'id',
                'username',
                'name',
                'extra_info->avatar AS avatar',
            )->orderByDesc('aura')->take(20)->get()
        ]);
    }

    public function manifest()
    {
        $json = json_decode(file_get_contents(base_path('resources/json/manifest.json')));

        $json->name = getSiteConfig('name');
        $json->gcm_sender_id = config('webpush.gcm.sender_id');
        $json->short_name = getSiteConfig('name');
        $json->description = getSiteConfig('slogan');

        foreach ($json->shortcuts as $shortcut) {
            if ("publish" === $shortcut->name) {
                $shortcut->name = __('Publish');
                $shortcut->short_name = __('Publish');
                $shortcut->url = route('writings.create');
            }

            if ("featured" === $shortcut->name) {
                $shortcut->name = __('Golden Flowers');
                $shortcut->short_name = __('Golden Flowers');
                $shortcut->url = route('writings.awards');
            }

            if ("random" === $shortcut->name) {
                $shortcut->name = __('Random');
                $shortcut->short_name = __('Random');
                $shortcut->url = route('writings.random');
            }
        }

        foreach ($json->related_applications as $app) {
            if ("play" === $app->platform) {
                $app->url = config('services.google.play_store.url');
                $app->id = config('services.google.play_store.id');
            }
        }

        $json->iarc_rating_id = config('services.compliance.iarc_rating_id');

        return $json;
    }

    public function offline()
    {
        Inertia::render('generic/PoOffline', [
            'meta' => []
        ]);
    }
}
