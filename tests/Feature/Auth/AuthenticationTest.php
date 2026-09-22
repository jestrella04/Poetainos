<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

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
        $password = fake()->password();
        $user = createUserWithPassword($password);

        // When
        $response = post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Then
        // AuthenticatedSessionController::store() returns the redirect target as JSON
        // for the frontend to navigate to, rather than an HTTP redirect response.
        assertAuthenticated();
        $response->assertOk();
        $response->assertJson(['redirect' => route('home')]);
    });

    it('honors a safe, same-site redirect target after login', function (): void {
        // Given
        $password = fake()->password();
        $user = createUserWithPassword($password);
        get('/login?redirect=/writings/create');

        // When
        $response = post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Then
        $response->assertJson(['redirect' => url('/writings/create')]);
    });

    it('ignores an external redirect target to prevent an open redirect', function (): void {
        // Given
        $password = fake()->password();
        $user = createUserWithPassword($password);
        get('/login?redirect=https://evil.example/phish');

        // When
        $response = post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Then
        $response->assertJson(['redirect' => route('home')]);
    });

    it('does not authenticate with an invalid password', function (): void {
        // Given
        $password = fake()->password();
        $user = createUserWithPassword($password);

        // When
        post('/login', [
            'email' => $user->email,
            'password' => strrev($password).fake()->password(),
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

describe('checking whether an email has an account', function (): void {
    it('reports whether the email exists', function (): void {
        // Given
        $user = createUser();

        // When
        $known = postJson(route('email.check'), ['email' => $user->email]);
        $unknown = postJson(route('email.check'), ['email' => fake()->unique()->safeEmail()]);

        // Then
        $known->assertOk()->assertJson(['exists' => true]);
        $unknown->assertOk()->assertJson(['exists' => false]);
    });

    it('is throttled so it cannot be used to enumerate accounts', function (): void {
        // When
        foreach (range(1, 10) as $attempt) {
            postJson(route('email.check'), ['email' => fake()->unique()->safeEmail()]);
        }
        $response = postJson(route('email.check'), ['email' => fake()->unique()->safeEmail()]);

        // Then
        $response->assertTooManyRequests();
    });
});
