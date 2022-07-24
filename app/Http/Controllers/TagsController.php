<?php

namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Query list of matching resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function query()
    {
        $wildcard = '%'. request('query') .'%';

        return Tag::where('name', 'like', $wildcard)
            ->take($this->pagination)
            ->get()
            ->map(function($tag, $key) {
                return [
                    "value" => $tag['name'],
                    "label" => $tag['name'],
                ];
            });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';

        $params = [
            'head_msg' => __('You are browsing the library of writings tagged with ":tag".', ['tag' => $tag->name]),
            'title' => getPageTitle([
                $tag->name,
                __('Tags'),
                ]),
        ];

        if ('latest' === $sort) {
            $writings = $tag->writings()
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = $tag->writings()
            ->orderBy('views', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('likes' === $sort) {
            $writings = $tag->writings()
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->simplePaginate($this->pagination);
        }

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params,
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function edit(Tag $tag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tag $tag)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return [
            'message' => __('Tag deleted successfully')
        ];
    }
}
