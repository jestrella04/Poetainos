<?php

use App\Jobs\RecalculateAura;
use App\Models\Comment;
use App\Models\Writing;
use App\Notifications\WritingFeatured;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

describe('a user\'s aura', function (): void {
    it('drops back to zero once they have no activity left', function (): void {
        // Given
        $user = createUser(['aura' => 5.0]);

        // When
        $user->updateAura();

        // Then
        expect((float) $user->refresh()->aura)->toBe(0.0);
    });

    it('counts the views the profile has right now', function (): void {
        // Given
        $user = createUser();
        DB::table('users')->where('id', $user->id)->update(['profile_views' => 100]);

        // When
        $user->updateAura();

        // Then
        expect((float) $user->refresh()->aura)->toBeGreaterThan(0.0);
    });

    it('is left alone when no point weights are configured', function (): void {
        // Given
        config(['poetainos.aura.points.user' => ['writing' => 0, 'like' => 0, 'comment' => 0, 'shelf' => 0, 'views' => 0, 'award' => 0]]);
        $user = createUser(['aura' => 5.0]);

        // When
        $user->updateAura();

        // Then
        expect((float) $user->refresh()->aura)->toBe(5.0);
    });
});

describe('a user\'s karma grade', function (): void {
    it('follows the points earned in the last ninety days', function (int $likes, string $grade): void {
        // Given
        config(['poetainos.aura.points.user.like' => 1000]);
        $user = createUser();
        Writing::factory()->count($likes)->create()
            ->each(fn (Writing $writing) => $writing->likes()->create(['user_id' => $user->id, 'vote' => 1]));

        // When
        $user->updateKarma();

        // Then
        expect($user->refresh()->karma)->toBe($grade);
    })->with([
        'nothing' => [0, 'F'],
        'one thousand points' => [1, 'D'],
        'two thousand points' => [2, 'C'],
        'three thousand points' => [3, 'B'],
        'four thousand points' => [4, 'A'],
    ]);

    it('ignores activity older than ninety days', function (): void {
        // Given
        config(['poetainos.aura.points.user.like' => 5000]);
        $user = createUser();
        $writing = Writing::factory()->create();
        $like = $writing->likes()->create(['user_id' => $user->id, 'vote' => 1]);
        DB::table('likes')->where('id', $like->id)->update(['created_at' => now()->subDays(91)]);

        // When
        $user->updateKarma();

        // Then
        expect($user->refresh()->karma)->toBe('F');
    });
});

describe('a writing\'s aura', function (): void {
    it('goes back down when a like is withdrawn', function (): void {
        // Given
        $writing = Writing::factory()->create(['views' => 0]);
        $liker = createUser();

        // When
        actingAs($liker)->post("/likes/writing/{$writing->id}/store");
        $withLike = (float) $writing->refresh()->aura;
        actingAs($liker)->post("/likes/writing/{$writing->id}/store");

        // Then
        expect($withLike)->toBeGreaterThan(0.0);
        expect((float) $writing->refresh()->aura)->toBe(0.0);
    });

    it('goes back down when a comment is deleted', function (): void {
        // Given
        $writing = Writing::factory()->create(['views' => 0]);
        $comment = Comment::factory()->for($writing)->create();
        $writing->updateAura();
        $withComment = (float) $writing->refresh()->aura;

        // When
        actingAs($comment->author)->delete("/comments/delete/{$comment->id}")->assertOk();

        // Then
        expect($withComment)->toBeGreaterThan(0.0);
        expect((float) $writing->refresh()->aura)->toBe(0.0);
    });

    it('goes back down when a writing is taken off the shelf', function (): void {
        // Given
        $writing = Writing::factory()->create(['views' => 0]);
        $reader = createUser();
        actingAs($reader)->post("/shelves/{$writing->slug}/store");
        $onShelf = (float) $writing->refresh()->aura;

        // When
        actingAs($reader)->post("/shelves/{$writing->slug}/store");

        // Then
        expect($onShelf)->toBeGreaterThan(0.0);
        expect((float) $writing->refresh()->aura)->toBe(0.0);
    });
});

describe('featuring a writing on the home page', function (): void {
    beforeEach(function (): void {
        config(['poetainos.aura.min_at_home' => 0.01]);
    });

    it('happens once and announces it to the author once', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['views' => 50]);

        // When
        $writing->updateAura();
        $firstAward = $writing->refresh()->home_posted_at;
        $writing->updateAura();
        $writing->updateAura();

        // Then
        expect($firstAward)->not->toBeNull();
        expect($writing->refresh()->home_posted_at)->toBe($firstAward);
        Notification::assertSentToTimes($author, WritingFeatured::class, 1);
    });

    it('does not happen for a writing older than a month', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create(['views' => 50, 'created_at' => now()->subDays(40)]);

        // When
        $writing->updateAura();

        // Then
        expect($writing->refresh()->home_posted_at)->toBeNull();
        Notification::assertNothingSent();
    });

    it('does not happen when the aura stays below the minimum', function (): void {
        // Given
        config(['poetainos.aura.min_at_home' => 1000]);
        $writing = Writing::factory()->create(['views' => 50]);

        // When
        $writing->updateAura();

        // Then
        expect($writing->refresh()->home_posted_at)->toBeNull();
        expect((float) $writing->aura)->toBeGreaterThan(0.0);
    });
});

describe('recalculating aura after an interaction', function (): void {
    it('is queued for the liker and the writing when a writing is liked', function (): void {
        // Given
        Queue::fake();
        $writing = Writing::factory()->create(['views' => 0]);
        $liker = createUser();

        // When
        actingAs($liker)->post("/likes/writing/{$writing->id}/store");

        // Then
        Queue::assertPushed(RecalculateAura::class, fn (RecalculateAura $job): bool => $job->user?->is($liker) === true
            && $job->writing?->is($writing) === true);
        expect((float) $writing->refresh()->aura)->toBe(0.0);
    });

    it('is queued for the liker only when a comment is liked', function (): void {
        // Given
        Queue::fake();
        $comment = Comment::factory()->create();
        $liker = createUser();

        // When
        actingAs($liker)->post("/likes/comment/{$comment->id}/store");

        // Then
        Queue::assertPushed(RecalculateAura::class, fn (RecalculateAura $job): bool => $job->user?->is($liker) === true
            && $job->writing === null);
    });

    it('is queued when a writing is shelved', function (): void {
        // Given
        Queue::fake();
        $writing = Writing::factory()->create();
        $reader = createUser();

        // When
        actingAs($reader)->post("/shelves/{$writing->slug}/store");

        // Then
        Queue::assertPushed(RecalculateAura::class, fn (RecalculateAura $job): bool => $job->user?->is($reader) === true
            && $job->writing?->is($writing) === true);
    });

    it('is queued when a comment is posted and when it is deleted', function (): void {
        // Given
        Queue::fake();
        $writing = Writing::factory()->create();
        $commenter = createUser();

        // When
        actingAs($commenter)->post('/comments/create', ['writing_id' => $writing->id, 'comment' => 'Lovely.']);
        $comment = Comment::firstOrFail();
        actingAs($commenter)->delete("/comments/delete/{$comment->id}");

        // Then
        Queue::assertPushed(RecalculateAura::class, 2);
    });
});
