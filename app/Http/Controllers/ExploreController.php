<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    private const TAGS_SHOWN = 20;

    private const AUTHORS_SHOWN = 20;

    public function index(): Response
    {
        $categories = Category::withCount('writings')
            ->has('writings')
            ->orderByDesc('writings_count')
            ->get();

        return Inertia::render('generic/PoExploreIndex', [
            'meta' => [
                'title' => getPageTitle([__('Explore')]),
            ],
            'totals' => [
                'writings' => Writing::count(),
                'authors' => User::has('writings')->count(),
            ],
            'categories' => [
                'main' => $categories->whereNull('parent_id')->values(),
                'alt' => $categories->whereNotNull('parent_id')->values(),
            ],
            'tags' => Tag::withCount('writings')
                ->orderByDesc('writings_count')
                ->has('writings')
                ->take(self::TAGS_SHOWN)
                ->get(),
            'authors' => User::forAuthorSummary(withKarma: true)
                ->ranked()
                ->take(self::AUTHORS_SHOWN)
                ->get(),
        ]);
    }
}
