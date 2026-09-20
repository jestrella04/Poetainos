<?php

use App\Models\User;

describe('the user factory', function (): void {
    it('produces users with no name, a first name or a full name', function (): void {
        // Given
        $users = User::factory()->count(60)->make();

        // When
        $wordCounts = $users->map(fn (User $user): int => $user->name === null ? 0 : count(explode(' ', $user->name)))->unique();

        // Then
        expect($wordCounts->sort()->values()->all())->toBe([0, 1, 2]);
    });

    it('gives about half of the users a bio that fits the profile limit', function (): void {
        // Given
        $users = User::factory()->count(60)->make();

        // When
        $bios = $users->map(fn (User $user): ?string => $user->extra_info['bio'] ?? null);

        // Then
        $withBio = $bios->filter(fn (?string $bio): bool => $bio !== null);
        expect($withBio)->not->toBeEmpty()
            ->and($withBio->count())->toBeLessThan($users->count())
            ->and($withBio->every(fn (string $bio): bool => mb_strlen($bio) >= 3 && mb_strlen($bio) <= 300))->toBeTrue();
    });
});
