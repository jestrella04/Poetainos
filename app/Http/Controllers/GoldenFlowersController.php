<?php

namespace App\Http\Controllers;

use App\Models\Writing;
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
            $writings = Writing::withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->simplePaginate($this->pagination);
        }

        $params = [
            'head_msg' => __('You are browsing the library of writings awarded with a Golden Flower.'),
            'title' => getPageTitle([__('Golden Flowers')]),
        ];

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params,
            'sort' => $sort,
        ]);
    }
}
