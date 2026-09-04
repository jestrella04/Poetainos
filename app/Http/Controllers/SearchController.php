<?php

namespace App\Http\Controllers;

class SearchController extends Controller
{
    public function show()
    {
        $query = request('q') ?? false;

        $params = [
            'title' => getPageTitle([__('Search')]),
            'query' => $query,
        ];

        return view('search.index', [
            'params' => $params,
        ]);
    }
}
