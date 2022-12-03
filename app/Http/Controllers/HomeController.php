<?php

namespace App\Http\Controllers;

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
}
