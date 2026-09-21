<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Writing;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
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
        $wildcard = '%'.escapeLike((string) request('query')).'%';

        return Tag::where('name', 'like', $wildcard)
            ->take($this->pagination)
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
     * @return Response|Paginator<int, Writing>
     */
    public function show(Tag $tag): Response|Paginator
    {
        $sort = resolveSort(['latest', 'popular', 'likes']);

        return $this->writingsIndex(
            $tag->writings()->visibleTo($this->getBlockedUsers())->withListingRelations()->sorted($sort),
            $sort,
            ['title' => getPageTitle([$tag->name, __('Tags')]), 'canonical' => $tag->path()],
            isDeferred: false,
        );
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
