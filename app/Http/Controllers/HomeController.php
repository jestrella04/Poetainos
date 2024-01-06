<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function offline()
    {
        return view('offline.index', [
            'params' => [
                'title' => getPageTitle([__('Offline')]),
            ]
        ]);
    }

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

    public function socialite()
    {
        return Inertia::render('auth/PoLogin', []);
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

    public function loginEmailPost()
    {
        // Validate user input
        request()->validate([
            'email' => 'required|email',
        ]);

        return ['exists' => User::where('email', request('email'))->count() > 0];
    }
}
