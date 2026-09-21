<?php

use App\Models\User;

describe('user initials', function (): void {
    it('uses the first letters of the first and last words of the name', function (): void {
        // Given
        $user = User::factory()->make(['name' => 'jane  Mary Doe', 'username' => 'janed']);

        // When
        $initials = $user->initials();

        // Then
        expect($initials)->toBe('JD');
    });

    it('uses the first letter of the name when it is a single word', function (): void {
        // Given
        $user = User::factory()->make(['name' => 'ángel', 'username' => 'janed']);

        // When
        $initials = $user->initials();

        // Then
        expect($initials)->toBe('Á');
    });

    it('falls back to the username when the name is empty', function (?string $name): void {
        // Given
        $user = User::factory()->make(['name' => $name, 'username' => 'janed']);

        // When
        $initials = $user->initials();

        // Then
        expect($initials)->toBe('J');
    })->with([null, ' ']);
});

describe('the X (Twitter) handle', function (): void {
    it('is the stored handle with a single @, or the display name without one', function (?string $stored, string $expected): void {
        // Given
        $user = createUser(['name' => 'Emily Dickinson', 'extra_info' => $stored === null ? null : ['social' => ['twitter' => $stored]]]);

        // Then
        expect($user->twitterHandleOrName())->toBe($expected);
    })->with([
        'a bare handle' => ['emily', '@emily'],
        'a handle typed with an @' => ['@emily', '@emily'],
        'an empty handle' => ['', 'Emily Dickinson'],
        'no social links' => [null, 'Emily Dickinson'],
    ]);
});
