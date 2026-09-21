<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Writing;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;
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
        $sort = resolveSort(['latest', 'popular', 'likes']);

        return $this->writingsIndex(
            $category->writingsRecursive()
                ->visibleTo($this->getBlockedUsers())
                ->withListingRelations()
                ->sorted($sort),
            $sort,
            [
                'title' => getPageTitle([$category->name, __('Categories')]),
                'canonical' => $category->path(),
                'description' => $category->description,
            ],
            isDeferred: false,
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array<string, mixed>
     */
    public function update(Request $request): array
    {
        // Get category model
        $category = Category::where('id', request('id'))->firstOrNew();

        // A category can't be moved under itself or one of its own descendants
        $invalidParentIds = $category->exists ? $category->descendantsAndSelf()->pluck('id')->all() : [];

        // Validate user input
        request()->validate([
            'id' => 'required|integer',
            'name' => ['required', 'string', Rule::unique('App\Models\Category')->ignore($category), 'min:3', 'max:40'],
            'parent' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn($invalidParentIds)],
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
     * @return array<string, string>
     */
    public function destroy(Category $category): array
    {
        $category->delete();

        return [
            'message' => __('Category deleted successfully'),
        ];
    }
}
