<?php

namespace Tests\Browser\Pages;

use Pest\Browser\Api\PendingAwaitablePage;

/**
 * Base Page Object: wraps the Pest browser page. Concrete pages define their
 * locators and actions only; assertions live in the tests, made against
 * browser().
 */
abstract class Page
{
    public function __construct(protected readonly PendingAwaitablePage $browser) {}

    protected static function visitUrl(string $url): PendingAwaitablePage
    {
        return visit($url);
    }

    public function browser(): PendingAwaitablePage
    {
        return $this->browser;
    }
}
