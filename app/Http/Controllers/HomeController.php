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
        return Inertia::render('explore/PoIndex', [
            'title' => getPageTitle([__('Explore')]),
            'categories' => [
                'main' => Category::main(),
                'secondary' => Category::secondary()
            ],
            'tags' => Tag::popular(20),
            'authors' => User::featured(20)
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

    public function loginEmailCheck()
    {
        return view('auth.email_check');
    }

    public function loginEmailPost()
    {
        // Validate user input
        request()->validate([
            'email' => 'required|email',
        ]);

        $email = request('email');
        $emailExists = User::where('email', $email)->count() > 0;

        if ($emailExists) {
            return to_route('login')->with(['email' => $email])->withInput();
        }

        return to_route('register')->with(['email' => $email])->withInput();
    }
}
