<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function explore()
    {
        return Inertia::render('explore/PoExploreIndex', [
            'title' => getPageTitle([__('Explore')]),
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
