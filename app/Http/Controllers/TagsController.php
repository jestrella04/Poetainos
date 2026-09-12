<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Writing;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class TagsController extends Controller
{
    /**
     * Query list of matching resources.
     *
     * @return Collection<int, array{value: mixed, label: mixed}>
     */
    public function query(): Collection
    {
        $wildcard = '%'.request('query').'%';

        return Tag::where('name', 'like', $wildcard)
            ->take($this->pagination ?? 15)
            ->get()
            ->map(function ($tag, $key) {
                return [
                    'value' => $tag['name'],
                    'label' => $tag['name'],
                ];
            });
    }

    /**
     * Display the specified resource.
     *
     * @return Response|Paginator<int, Writing&object{pivot: Pivot}>
     */
    public function show(Tag $tag): Response|Paginator
    {
        $sort = resolveSort(['latest', 'popular', 'likes']);
        $writings = $tag->writings()
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
                    $tag->name,
                    __('Tags'),
                ]),
                'canonical' => route('home'),
            ],
            'writings' => $writings,
            'sort' => $sort,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Tag $tag): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array<string, string>
     */
    public function destroy(Tag $tag): array
    {
        $tag->delete();

        return [
            'message' => __('Tag deleted successfully'),
        ];
    }
}
