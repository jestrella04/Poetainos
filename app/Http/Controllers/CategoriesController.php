<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Category $category)
    {
        $sort = resolveSort(['latest', 'popular', 'likes']);
        $params = [
            'head_msg' => __('You are browsing the library of writings under the ":category" category.', ['category' => $category->name]).' '.$category->description,
        ];

        $writings = $category->writingsRecursive()
            ->visibleTo($this->getBlockedUsers())
            ->withListingRelations()
            ->sorted($sort)
            ->simplePaginate($this->pagination)
            ->withQueryString();

        if (request()->expectsJson()) {
            return $writings;
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => getPageTitle([
                    $category->name,
                    __('Categories'),
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
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
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

        $action = $category->exists ? 'update' : 'create';

        // Update accordingly
        $category->name = request('name');
        $category->parent_id = request('parent');
        $category->description = request('description');

        if ($action === 'create') {
            $category->slug = slugify($category->getTable(), request('name'));
        }

        $category->save();

        $message = $action === 'create'
            ? __('Category created successfully')
            : __('Category updated successfully');

        return [
            'message' => $message,
            'action' => $action,
            'id' => $category->id,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return [
            'message' => __('Category deleted successfully'),
        ];
    }
}
