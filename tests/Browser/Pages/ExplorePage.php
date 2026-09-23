<?php

namespace Tests\Browser\Pages;

use App\Models\Category;

class ExplorePage extends Page
{
    public const TITLE = '#explore-title';

    public static function open(): self
    {
        return new self(static::visitUrl(route('explore')));
    }

    public static function categoryName(Category $category): string
    {
        return "#category-{$category->id}-name";
    }
}
