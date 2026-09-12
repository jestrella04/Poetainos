<?php

use App\Providers\RouteServiceProvider;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

describe('the login screen', function (): void {
    it('can be rendered', function (): void {
        // When
        $response = get('/login');

        // Then
        $response->assertStatus(200);
    });
});

describe('authenticating', function (): void {
    it('allows users to authenticate using the login screen', function (): void {
        // Given
        $user = createUser();

        // When
        $response = post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Then
        // AuthenticatedSessionController::store() returns the redirect target as JSON
        // for the frontend to navigate to, rather than an HTTP redirect response.
        assertAuthenticated();
        $response->assertOk();
        $response->assertJson(['redirect' => url(RouteServiceProvider::HOME)]);
    });

    it('does not authenticate with an invalid password', function (): void {
        // Given
        $user = createUser();

        // When
        post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // Then
        assertGuest();
    });
});

describe('logging out', function (): void {
    it('allows users to logout', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->post('/logout');

        // Then
        assertGuest();
        $response->assertRedirect('/');
    });
});
