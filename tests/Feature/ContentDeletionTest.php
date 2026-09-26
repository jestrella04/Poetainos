<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Writing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;

describe('deleting a writing', function (): void {
    it('also deletes its likes and the notifications about it, but keeps unrelated ones', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $otherWriting = Writing::factory()->create();
        $writing->likes()->create(['user_id' => createUser()->id, 'vote' => 1]);
        $otherWriting->likes()->create(['user_id' => createUser()->id, 'vote' => 1]);
        createDatabaseNotification($author, ['writing_id' => $writing->id]);
        createDatabaseNotification($author, ['writing_id' => $otherWriting->id]);

        // When
        $response = actingAs($author)->delete('/writings/delete/'.$writing->slug);

        // Then
        $response->assertOk();
        assertDatabaseMissing('likes', ['likeable_type' => Writing::class, 'likeable_id' => $writing->id]);
        expect(Like::count())->toBe(1);
        expect(DB::table('notifications')->pluck('data')->map(fn (string $data): array => json_decode($data, true))->all())
            ->toBe([['writing_id' => $otherWriting->id]]);
    });
});

describe('deleting a comment', function (): void {
    it('also deletes its likes and the notifications about it', function (): void {
        // Given
        $writingAuthor = createUser();
        $commenter = createUser();
        $writing = Writing::factory()->for($writingAuthor, 'author')->create();
        $comment = Comment::factory()->for($writing)->for($commenter, 'author')->create();
        $comment->likes()->create(['user_id' => createUser()->id, 'vote' => 1]);
        createDatabaseNotification($writingAuthor, ['writing_id' => $writing->id, 'comment_id' => $comment->id]);

        // When
        $response = actingAs($commenter)->delete("/comments/delete/{$comment->id}");

        // Then
        $response->assertOk();
        assertDatabaseCount('likes', 0);
        assertDatabaseCount('notifications', 0);
    });
});

describe('deleting a user', function (): void {
    it('also deletes their likes, their notifications and the notifications about them', function (): void {
        // Given
        $user = createUser();
        $recipient = createUser();
        Writing::factory()->create()->likes()->create(['user_id' => $user->id, 'vote' => 1]);
        createDatabaseNotification($user, ['user_id' => $recipient->id]);
        createDatabaseNotification($recipient, ['user_id' => $user->id]);
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->delete('/admin/users/delete/'.$user->username);

        // Then
        $response->assertOk();
        expect(User::find($user->id))->toBeNull();
        assertDatabaseCount('likes', 0);
        assertDatabaseCount('notifications', 0);
    });
});

describe('deleting a writing with comments', function (): void {
    it('also deletes the likes of its comments and the notifications about them', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $comment = Comment::factory()->for($writing)->create();
        $comment->likes()->create(['user_id' => createUser()->id, 'vote' => 1]);
        createDatabaseNotification($comment->author()->firstOrFail(), ['comment_id' => $comment->id]);

        // When
        actingAs($author)->delete('/writings/delete/'.$writing->slug)->assertOk();

        // Then
        assertDatabaseCount('likes', 0);
        assertDatabaseCount('notifications', 0);
    });
});

describe('deleting a user with content', function (): void {
    it('also deletes what others left on their writings and comments, and their images', function (): void {
        // Given
        Storage::fake('local');
        Storage::disk('local')->put('covers/cover.jpg', 'cover');
        Storage::disk('local')->put('avatars/avatar.png', 'avatar');
        $user = createUser();
        UserProfile::factory()->for($user)->create(['avatar' => 'avatars/avatar.png']);
        $reader = createUser();
        $writing = Writing::factory()->for($user, 'author')->create(['cover' => 'covers/cover.jpg']);
        $commentOnTheirWriting = Comment::factory()->for($writing)->for($reader, 'author')->create();
        $theirComment = Comment::factory()->for(Writing::factory())->for($user, 'author')->create();
        $writing->likes()->create(['user_id' => $reader->id, 'vote' => 1]);
        $commentOnTheirWriting->likes()->create(['user_id' => $reader->id, 'vote' => 1]);
        $theirComment->likes()->create(['user_id' => $reader->id, 'vote' => 1]);
        createDatabaseNotification($reader, ['writing_id' => $writing->id]);
        createDatabaseNotification($reader, ['comment_id' => $theirComment->id]);

        // When
        actingAs(actingAsAdmin())->delete('/admin/users/delete/'.$user->username)->assertOk();

        // Then
        assertDatabaseCount('likes', 0);
        assertDatabaseCount('notifications', 0);
        Storage::disk('local')->assertMissing(['covers/cover.jpg', 'avatars/avatar.png']);
    });
});

describe('the content:prune-orphans command', function (): void {
    it('deletes likes and notifications about missing content but keeps the rest', function (): void {
        // Given
        $recipient = createUser();
        $writing = Writing::factory()->create();
        $missingId = fake()->numberBetween(100000, 999999);
        $writing->likes()->create(['user_id' => $recipient->id, 'vote' => 1]);
        DB::table('likes')->insert([
            'likeable_type' => Comment::class,
            'likeable_id' => $missingId,
            'user_id' => $recipient->id,
            'vote' => 1,
            'created_at' => now(),
        ]);
        createDatabaseNotification($recipient, ['writing_id' => $writing->id, 'user_id' => $recipient->id]);
        createDatabaseNotification($recipient, ['writing_id' => $missingId]);
        createDatabaseNotification($recipient, ['comment_id' => $missingId]);
        createDatabaseNotification($recipient, ['user_id' => $missingId]);

        // When
        pendingArtisan('content:prune-orphans')->assertSuccessful();

        // Then
        expect(Like::sole()->likeable_id)->toBe($writing->id);
        expect(json_decode((string) DB::table('notifications')->sole()->data, true))
            ->toBe(['writing_id' => $writing->id, 'user_id' => $recipient->id]);
    });
});
