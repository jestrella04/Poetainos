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
            $writings = $writings->latest()->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = $writings->orderBy('views', 'desc')->simplePaginate($this->pagination);
        } elseif ('likes' === $sort) {
            $writings = $writings->orderBy('likes_count', 'desc')->simplePaginate($this->pagination);
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
            $writings = $writings->latest()->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = $writings->orderBy('views', 'desc')->simplePaginate($this->pagination);
        } elseif ('likes' === $sort) {
            $writings = $writings->orderBy('likes_count', 'desc')->simplePaginate($this->pagination);
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

    public function explore()
    {
        return Inertia::render('explore/PoExploreIndex', [
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

    public function sharer()
    {
        $title = request('title');
        $url = request('url');

        return view('partials.sharer', [
            'title' => $title,
            'url' => $url,
        ]);
    }
}
