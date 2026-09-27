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
     * Tags whose name matches the query, as select options.
     *
     * @return Collection<int, array{value: string, label: string}>
     */
    public function search(): Collection
    {
        $wildcard = '%'.escapeLike((string) request('query')).'%';

        return Tag::where('name', 'like', $wildcard)
            ->take($this->perPage)
            ->get()
            ->map(fn (Tag $tag): array => [
                'value' => $tag->name,
                'label' => $tag->name,
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @return Response|Paginator<int, Writing>
     */
    public function show(Tag $tag): Response|Paginator
    {
        return $this->writingsIndex(
            $tag->writings(),
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
