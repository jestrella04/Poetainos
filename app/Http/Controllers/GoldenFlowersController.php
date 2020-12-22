<?php

namespace App\Http\Controllers;

use App\Writing;
use Illuminate\Http\Request;

class GoldenFlowersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';

        if ('latest' === $sort) {
            $writings = Writing::whereNotNull('home_posted_at')
            ->latest()
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = Writing::whereNotNull('home_posted_at')
            ->orderBy('views', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('likes' === $sort) {
            $writings = Writing::withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->simplePaginate($this->pagination);
        }

        $params = [
            'title' => getPageTitle([__('Golden Flowers')]),
        ];

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params,
            'sort' => $sort,
        ]);
    }
}
