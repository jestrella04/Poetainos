<?php

namespace App\Http\Controllers;

use App\Category;
use App\Writing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $sort = in_array(request('sort'), ['latest', 'popular']) ? request('sort') : 'latest';

        $params = [
            'title' => $category->name,
            'description' => $category->description,
        ];

        if ('latest' === $sort) {
            $writings = $category->writingsRecursive()
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = $category->writingsRecursive()
            ->orderBy('views', 'desc')
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
        // Get category model
        $category = Category::where('id', request('id'))->firstOrNew();

        // Validate user input
        request()->validate([
            'id' => 'required|integer',
            'name' => ['required', 'string', Rule::unique('App\Category')->ignore($category), 'min:3', 'max:40'],
            'parent' => 'nullable|integer|exists:categories,id',
            'description' => 'required|string|min:3|max:255',
        ]);

        // Update accordingly
        $category->name = request('name');
        $category->parent_id = request('parent');
        $category->description = request('description');

        if (! $category->exists) {
            $action = 'create';
            $category->slug = slugify($category->getTable(), request('name'));
        }

        $category->save();

        if (isset($action) && 'create' === $action) {
            $message = __('Category created successfully');
        } else {
            $message = __('Category updated successfully');
        }

        return [
            'message' => $message,
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
        $category->delete();

        return [
            'message' => __('Category deleted successfully')
        ];
    }
}
