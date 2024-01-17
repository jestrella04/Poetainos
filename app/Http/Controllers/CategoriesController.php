<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Writing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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
     * @param  \App\Models\Category  $category
     * @return \Inertia\Response
     */
    public function show(Category $category)
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';
        $params = [
            'head_msg' => __('You are browsing the library of writings under the ":category" category.', ['category' => $category->name]) . ' ' . $category->description,
        ];

        $writings = $category->writingsRecursive()
            ->whereNotIn('user_id', $this->getBlockedUsers())
            ->withCount(['likes', 'comments', 'shelf'])
            ->with(['author' => function ($query) {
                $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
            }]);

        if ('latest' === $sort) {
            $writings = $writings->orderBy('created_at', 'desc')->simplePaginate($this->pagination)->withQueryString();
        } elseif ('popular' === $sort) {
            $writings = $writings->orderBy('views', 'desc')->simplePaginate($this->pagination)->withQueryString();
        } elseif ('likes' === $sort) {
            $writings = $writings->orderBy('likes_count', 'desc')->simplePaginate($this->pagination)->withQueryString();
        }

        if (request()->expectsJson()) {
            return $writings;
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => getPageTitle([
                    $category->name,
                    __('Categories')
                ]),
                'canonical' => route('home'),
                'description' => $category->description,
            ],
            'writings' => $writings,
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
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
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        // Get category model
        $category = Category::where('id', request('id'))->firstOrNew();

        // Validate user input
        request()->validate([
            'id' => 'required|integer',
            'name' => ['required', 'string', Rule::unique('App\Models\Category')->ignore($category), 'min:3', 'max:40'],
            'parent' => 'nullable|integer|exists:categories,id',
            'description' => 'required|string|min:3|max:255',
        ]);

        // Update accordingly
        $category->name = request('name');
        $category->parent_id = request('parent');
        $category->description = request('description');

        if (!$category->exists) {
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
     * @param  \App\Models\Category  $category
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
