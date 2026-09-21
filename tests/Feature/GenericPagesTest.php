<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('the offline page', function (): void {
    it('renders', function (): void {
        // When
        $response = get('/offline');

        // Then
        $response->assertOk();
    });
});

describe('page titles', function (): void {
    beforeEach(function (): void {
        config(['inertia.testing.ensure_pages_exist' => false]);
    });

    it('carry the site name, like every other page', function (string $routeName, string $section, ?string $signIn): void {
        // When
        $response = $signIn === 'user'
            ? actingAs(createUser())->get(route($routeName))
            : get(route($routeName));

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page->where('meta.title', getPageTitle([__($section)])));
    })->with([
        'the contact form' => ['contact.create', 'Contact form', null],
        'the static pages index' => ['pages.index', 'Pages', null],
        'the offline page' => ['offline', 'Offline', null],
        'the notifications' => ['notifications.index', 'Notifications', 'user'],
    ]);
});
