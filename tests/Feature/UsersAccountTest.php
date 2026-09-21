<?php

use App\Models\BlockedUser;
use App\Models\Writing;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

describe('the account page', function (): void {
    it('summarises the account with counts and the registration date', function (): void {
        // Given
        $user = createUser();
        Writing::factory()->count(2)->for($user, 'author')->create();
        $user->shelf()->attach(Writing::factory()->create());
        BlockedUser::factory()->create(['user_id' => $user->id]);

        // When
        $response = actingAs($user)->get(route('users.account'));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/PoUsersAccount')
                ->where('account.writings_count', 2)
                ->where('account.shelf_count', 1)
                ->where('account.likes_count', 0)
                ->where('account.blocked_authors_count', 1)
                ->has('account.created_at')
                ->has('notifications.email'));
    });

    it('redirects guests to the login page', function (): void {
        // When
        $response = get(route('users.account'));

        // Then
        $response->assertRedirect();
    });
});
