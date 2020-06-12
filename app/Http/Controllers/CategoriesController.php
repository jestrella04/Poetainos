<?php

namespace App\Http\Controllers;

use App\Category;
use App\Writing;
use Illuminate\Http\Request;

class CategoriesController extends Controller
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
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $sort = request('sort') ?? 'latest';

        $params = [
            'title' => $category->name,
        ];

        if ('latest' === $sort) {
            $writings = $category->writings()
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = $category->writings()
            ->orderBy('views', 'desc')
            ->simplePaginate($this->pagination);
        }

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        // Validate user input
        request()->validate([
            'id' => 'required|integer',
            'title' => 'required|string|unique:categories,name|min:3|max:40',
            'description' => 'required|string|min:3|max:255',
        ]);

        // Get category model
        $category = Category::where('id', request('id'))->firstOrNew();

        // Update accordingly
        $category->name = request('title');
        $category->description = request('description');

        if (! $category->exists) {
            $action = 'create';
            $category->slug = slugify($category->getTable(), request('title'));
        }

        $category->save();

        return [
            'action' => $action ?? 'update',
            'id' => $category->id,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        //
    }
}
