<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('login screen can be rendered', function (): void {
    $response = get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->create();

    $response = post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // AuthenticatedSessionController::store() returns the redirect target as JSON
    // for the frontend to navigate to, rather than an HTTP redirect response.
    assertAuthenticated();
    $response->assertOk();
    $response->assertJson(['redirect' => url(RouteServiceProvider::HOME)]);
});

test('users can not authenticate with invalid password', function (): void {
    $user = User::factory()->create();

    post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    assertGuest();
});

test('users can logout', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/logout');

    assertGuest();
    $response->assertRedirect('/');
});
