<?php

use App\Models\BlockedUser;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

describe('the blocked authors list', function (): void {
    it('renders the blocked authors page for the authenticated user', function (): void {
        // Given
        $viewer = createUser();

        // When
        $response = actingAs($viewer)->get(route('users.blocked.index'));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('users/PoUsersBlockedIndex', false));
    });

    it("lists only the authenticated user's blocked authors", function (): void {
        // Given
        $viewer = createUser();
        $blocked = createUser();
        $otherViewer = createUser();
        BlockedUser::factory()->create(['user_id' => $viewer->id, 'blocked_user_id' => $blocked->id]);
        BlockedUser::factory()->create(['user_id' => $otherViewer->id]);

        // When
        $response = actingAs($viewer)->getJson(route('users.blocked.index'));

        // Then
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.username', $blocked->username);
    });

    it('redirects guests to the login page', function (): void {
        // When
        $response = get(route('users.blocked.index'));

        // Then
        $response->assertRedirect();
    });
});
