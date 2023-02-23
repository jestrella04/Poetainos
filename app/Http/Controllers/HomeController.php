<?php

namespace App\Http\Controllers;

use App\Models\User;

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
        return view('explore.index', [
            'params' => [
                'title' => getPageTitle([__('Explore')])
            ]
        ]);
    }

    public function socialite()
    {
        return view('auth.social');
    }

    public function sharer()
    {
        $title = request('title');
        $url = request('url');

        return view('partials.sharer',  [
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
