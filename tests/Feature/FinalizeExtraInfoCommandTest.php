<?php

use App\Models\Comment;
use App\Models\UserProfile;
use App\Models\Writing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\travel;

/**
 * A user whose extra_info bio was copied to their profile, as the copying migration leaves them.
 */
function userWithCopiedBio(string $bio): int
{
    $user = createUser();
    UserProfile::factory()->for($user)->create(['bio' => $bio, 'avatar' => null]);
    DB::table('users')->where('id', $user->id)->update(['extra_info' => json_encode(['bio' => $bio])]);

    return $user->id;
}

beforeEach(function (): void {
    Storage::fake('local');
});

describe('the extra-info:finalize command', function (): void {
    it('confirms a complete copy without changing anything when only verifying', function (): void {
        // Given
        userWithCopiedBio(fake()->sentence());

        // When
        pendingArtisan('extra-info:finalize', ['--verify-only' => true])
            ->expectsOutput('Values not copied: 0')
            ->assertSuccessful();

        // Then
        expect(Schema::hasColumn('users', 'extra_info'))->toBeTrue();
    });

    it('refuses to drop anything while a value was not copied', function (): void {
        // Given
        $user = createUser();
        DB::table('users')->where('id', $user->id)->update(['extra_info' => json_encode(['bio' => fake()->sentence()])]);
        userWithCopiedBio(fake()->sentence());

        // When
        pendingArtisan('extra-info:finalize', ['--force' => true])
            ->expectsOutput('Values not copied: 1')
            ->assertFailed();

        // Then
        expect(Schema::hasColumn('users', 'extra_info'))->toBeTrue();
        expect(Storage::disk('local')->allFiles('backups'))->toBe([]);
    });

    it('tells a profile the user edited after the copy apart from a lost value', function (): void {
        // Given
        $userId = userWithCopiedBio(fake()->sentence());
        travel(1)->hour();
        UserProfile::where('user_id', $userId)->firstOrFail()->update(['bio' => fake()->sentence()]);

        // When
        pendingArtisan('extra-info:finalize', ['--verify-only' => true])
            ->expectsOutput('Values users changed since then: 1')
            ->expectsOutput('Values not copied: 0')
            ->assertSuccessful();
    });

    it('archives the JSON, prunes orphans and drops the legacy columns once confirmed', function (): void {
        // Given
        $bio = fake()->sentence();
        $userId = userWithCopiedBio($bio);
        $writing = Writing::factory()->create(['cover' => null]);
        DB::table('likes')->insert([
            'likeable_type' => Comment::class,
            'likeable_id' => fake()->numberBetween(100000, 999999),
            'user_id' => $userId,
            'vote' => 1,
            'created_at' => Carbon::now(),
        ]);

        // When
        pendingArtisan('extra-info:finalize')
            ->expectsConfirmation('Archive and drop users.extra_info and writings.extra_info?', 'yes')
            ->assertSuccessful();

        // Then
        expect(Schema::hasColumn('users', 'extra_info'))->toBeFalse();
        expect(Schema::hasColumn('writings', 'extra_info'))->toBeFalse();
        expect(DB::table('likes')->count())->toBe(0);
        $archives = Storage::disk('local')->allFiles('backups');
        expect($archives)->toHaveCount(1);
        expect(json_decode((string) Storage::disk('local')->get($archives[0]), true)['users'][$userId])->toBe(['bio' => $bio]);
        expect($writing->refresh()->exists)->toBeTrue();
    });

    it('changes nothing when the confirmation is declined', function (): void {
        // Given
        userWithCopiedBio(fake()->sentence());

        // When
        pendingArtisan('extra-info:finalize')
            ->expectsConfirmation('Archive and drop users.extra_info and writings.extra_info?', 'no')
            ->assertFailed();

        // Then
        expect(Schema::hasColumn('users', 'extra_info'))->toBeTrue();
    });
});
