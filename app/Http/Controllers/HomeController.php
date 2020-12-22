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
}
