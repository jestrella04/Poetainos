<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Writing;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                ->visibleTo($this->blockedAuthorIds())
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
     * Store a newly created resource in storage.
     *
     * @return array{message: string, id: int}
     */
    public function store(): array
    {
        $category = new Category;

        $this->validateCategory($category);

        $category->slug = slugify($category->getTable(), request('name'));

        return $this->save($category, __('Category created successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array{message: string, id: int}
     */
    public function update(Category $category): array
    {
        $this->validateCategory($category);

        return $this->save($category, __('Category updated successfully'));
    }

    /**
     * Remove the specified resource from storage. A category that still has
     * writings or subcategories is kept, since deleting it would cascade to
     * them and leave its writings without a category.
     *
     * @return array<string, string>
     */
    public function destroy(Category $category): array
    {
        if ($category->writings()->exists() || $category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => __('A category with writings or subcategories cannot be deleted.'),
            ]);
        }

        $category->delete();

        return [
            'message' => __('Category deleted successfully'),
        ];
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

    /**
     * @return array{message: string, id: int}
     */
    private function save(Category $category, string $message): array
    {
        $category->name = request('name');
        $category->parent_id = request('parent');
        $category->description = request('description');
        $category->save();

        return [
            'message' => $message,
            'id' => $category->id,
        ];
    }
}
