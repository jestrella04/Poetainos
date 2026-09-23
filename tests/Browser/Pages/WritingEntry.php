<?php

namespace Tests\Browser\Pages;

use App\Models\Writing;

/**
 * Locators for a single writing entry (PoWritingsEntry), rendered both in
 * listings and on the writing's own page.
 */
class WritingEntry
{
    public function __construct(private readonly Writing $writing) {}

    public function title(): string
    {
        return $this->locator('title');
    }

    public function author(): string
    {
        return $this->locator('author');
    }

    public function likeButton(): string
    {
        return $this->locator('like');
    }

    public function shelveButton(): string
    {
        return $this->locator('shelve');
    }

    public function moreActionsButton(): string
    {
        return $this->locator('more');
    }

    public function editLink(): string
    {
        return $this->locator('edit');
    }

    private function locator(string $element): string
    {
        return "#writing-{$this->writing->id}-{$element}";
    }
}
