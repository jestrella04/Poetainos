<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Writing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CategoriesController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return Response|Paginator<int, Writing>
     */
    public function show(Category $category): Response|Paginator
    {
        return $this->writingsIndex(
            $category->writingsRecursive(),
            [
                'title' => getPageTitle([$category->name, __('Categories')]),
                'canonical' => $category->path(),
                'description' => $category->description,
            ],
            isDeferred: false,
        );
    }

    /**
     * Store a newly created resource in storage, then return to the admin table.
     */
    public function store(): RedirectResponse
    {
        $category = new Category;

        $this->validateCategory($category);

        retryOnSlugCollision(function () use ($category): void {
            $category->slug = slugify($category->getTable(), request('name'));
            $this->save($category);
        });

        Inertia::flash(['message' => 'categories.category-created', 'color' => 'success']);

        return back();
    }

    /**
     * Update the specified resource in storage, then return to the admin table.
     */
    public function update(Category $category): RedirectResponse
    {
        $this->validateCategory($category);
        $this->save($category);

        Inertia::flash(['message' => 'categories.category-updated', 'color' => 'success']);

        return back();
    }

    /**
     * Remove the specified resource from storage, then return to the admin
     * table. A category that still has writings or subcategories is kept,
     * since deleting it would cascade to them and leave its writings without
     * a category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->writings()->exists() || $category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => __('A category with writings or subcategories cannot be deleted.'),
            ]);
        }

        $category->delete();

        Inertia::flash(['message' => 'categories.category-deleted', 'color' => 'success']);

        return back();
    }

    /**
     * A category can't be moved under itself or one of its own descendants.
     */
    private function validateCategory(Category $category): void
    {
        $invalidParentIds = $category->exists ? $category->descendantsAndSelf()->pluck('id')->all() : [];

        request()->validate([
            'name' => ['required', 'string', Rule::unique(Category::class)->ignore($category), 'min:3', 'max:40'],
            'parent' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn($invalidParentIds)],
            'description' => 'required|string|min:3|max:255',
        ]);
    }

    private function save(Category $category): void
    {
        $category->name = request('name');
        $category->parent_id = request('parent');
        $category->description = request('description');
        $category->save();
    }
}
