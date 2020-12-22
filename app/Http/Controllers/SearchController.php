<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            'params' => $params
        ]);
    }
}
