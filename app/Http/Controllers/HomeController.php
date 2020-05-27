<?php

namespace App\Http\Controllers;

use App\Writing;

class HomeController extends Controller
{
    public function show()
    {
        $writings = Writing::where('aura', '>', $this->auraHome)
        ->orderBy('home_posted_at', 'desc')
        ->simplePaginate($this->pagination);

        $params = [
            'title' => __('Home'),
            'empty-head' => __('Still doing the math'),
            'empty-msg' => __('Our algorithm will rank writings based on flags like hits, votes, comments and how many times they are shelved.'),
            'empty-icon' => 'dove'
        ];

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params
        ]);
    }
}
