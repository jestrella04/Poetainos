<?php

use App\Models\Writing;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('the author profile', function (): void {
    beforeEach(function (): void {
        // Components live under resources/js/components, not Inertia's default Pages directory,
        // and reloadOnly() re-checks that the component file exists.
        config(['inertia.testing.ensure_pages_exist' => false]);
    });

    it('defers the author writings and drops the random sidebar selection', function (): void {
        // Given
        $author = createUser();
        Writing::factory()->for($author, 'author')->create();

        // When
        $response = get(route('users.show', $author->username));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/PoUsersShow')
                ->missing('authorWritings')
                ->missing('writings.from_author')
                ->has('writings.from_shelf')
                ->has('writings.from_liked'));
    });

    it('lists the author writings latest first, paginated through the writings endpoint', function (): void {
        // Given
        $author = createUser();
        $older = Writing::factory()->for($author, 'author')->create(['created_at' => now()->subDay()]);
        $newer = Writing::factory()->for($author, 'author')->create(['created_at' => now()]);
        Writing::factory()->for(createUser(), 'author')->create();

        // When
        $response = get(route('users.show', $author->username));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->reloadOnly('authorWritings', fn ($reloaded) => $reloaded
                    ->has('authorWritings.data', 2)
                    ->where('authorWritings.data.0.id', $newer->id)
                    ->where('authorWritings.data.1.id', $older->id)
                    ->where('authorWritings.data.0.author.username', $author->username)
                    ->where('authorWritings.path', route('users.writings.index', $author->username))));
    });

    it('links the next page to the writings endpoint', function (): void {
        // Given
        $author = createUser();
        Writing::factory()->count(30)->for($author, 'author')->create();

        // When
        $response = get(route('users.show', $author->username));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->reloadOnly('authorWritings', fn ($reloaded) => $reloaded
                    ->where(
                        'authorWritings.next_page_url',
                        route('users.writings.index', $author->username).'?page=2',
                    )));
    });

    it('lists no writings of an author the viewer has blocked', function (): void {
        // Given
        $author = createUser();
        $viewer = createUser();
        Writing::factory()->for($author, 'author')->create();
        $viewer->block($author);

        // When
        $response = actingAs($viewer)->get(route('users.show', $author->username));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('isAuthorBlocked', true)
                ->reloadOnly('authorWritings', fn ($reloaded) => $reloaded
                    ->has('authorWritings.data', 0)));
    });

    it('exposes the occupation of the author', function (): void {
        // Given
        $author = createUser();
        $author->forceFill(['extra_info' => ['occupation' => 'Traductora']])->save();

        // When
        $response = get(route('users.show', $author->username));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page->where('user.occupation', 'Traductora'));
    });

    it('exposes the social links of the author as a JSON object', function (): void {
        // Given
        $author = createUser();
        $author->forceFill(['extra_info' => ['social' => ['twitter' => 'marisol', 'instagram' => '']]])->save();

        // When
        $response = get(route('users.show', $author->username));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page->where(
                'user.social',
                fn ($social) => json_decode($social, true) === ['twitter' => 'marisol', 'instagram' => ''],
            ));
    });

    it('exposes empty social links when the author has none', function (): void {
        // Given
        $author = createUser();

        // When
        $response = get(route('users.show', $author->username));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page->where('user.social', '[]'));
    });
});
