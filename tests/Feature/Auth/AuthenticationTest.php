<?php

use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;
use function Pest\Laravel\travel;
use function Pest\Laravel\withSession;

describe('the login screen', function (): void {
    it('can be rendered', function (): void {
        // When
        $response = get('/login');

        // Then
        $response->assertStatus(200);
    });

    it('fills in the address a password was just reset for', function (): void {
        // Given
        $email = fake()->safeEmail();

        // When
        $response = withSession(['email' => $email])->get('/login');

        // Then
        $response->assertInertia(fn ($page) => $page->where('email', $email));
    });

    it('opens on the email step after a password reset', function (): void {
        // When
        $response = get(route('login', ['isReset' => 1, 'isEmail' => 1]));

        // Then
        $response->assertInertia(fn ($page) => $page
            ->where('startsWithEmail', true)
            ->where('isAfterPasswordReset', true));
    });

    it('opens on the sign-in choices otherwise', function (): void {
        // When
        $response = get(route('login'));

        // Then
        $response->assertInertia(fn ($page) => $page
            ->where('startsWithEmail', false)
            ->where('isAfterPasswordReset', false));
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
        assertAuthenticated();
        $response->assertRedirect(route('home'))->assertInertiaFlash('message', 'accounts.welcome-back');
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
        $response->assertRedirect(url('/writings/create'));
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
        $response->assertRedirect(route('home'));
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

describe('failed logins from one address', function (): void {
    it('lock the address out across every account it tries', function (): void {
        // Given
        $password = fake()->password();
        $user = createUserWithPassword($password);
        foreach (range(1, 20) as $attempt) {
            post('/login', ['email' => fake()->unique()->safeEmail(), 'password' => fake()->password()]);
        }

        // When
        $response = post('/login', ['email' => $user->email, 'password' => $password]);

        // Then
        $response->assertSessionHasErrors('email');
        assertGuest();
    });

    it('lock an account out after five wrong passwords', function (): void {
        // Given
        $password = fake()->password();
        $user = createUserWithPassword($password);
        foreach (range(1, 5) as $attempt) {
            post('/login', ['email' => $user->email, 'password' => strrev($password).fake()->password()]);
        }

        // When
        post('/login', ['email' => $user->email, 'password' => $password]);

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

describe('an open session', function (): void {
    it('ends once the password it was opened with changes', function (): void {
        // Given
        $user = createUser();
        $replacedPasswordHash = auth()->guard('web')->hashPasswordForCookie(Hash::make(fakeStrongPassword()));

        // When
        $response = actingAs($user)
            ->withSession(['password_hash_web' => $replacedPasswordHash])
            ->get(route('users.account'));

        // Then
        $response->assertRedirect(route('login'));
        assertGuest();
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
        foreach (range(1, 5) as $attempt) {
            postJson(route('email.check'), ['email' => fake()->unique()->safeEmail()]);
        }
        $response = postJson(route('email.check'), ['email' => fake()->unique()->safeEmail()]);

        // Then
        $response->assertTooManyRequests();
    });

    it('caps how many addresses one visitor can check in a day', function (): void {
        // Given
        foreach (range(1, 10) as $minute) {
            foreach (range(1, 5) as $attempt) {
                postJson(route('email.check'), ['email' => fake()->unique()->safeEmail()]);
            }
            travel(1)->minutes();
        }

        // When
        $response = postJson(route('email.check'), ['email' => fake()->unique()->safeEmail()]);

        // Then
        $response->assertTooManyRequests();
    });
});
