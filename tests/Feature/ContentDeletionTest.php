<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Support\Facades\DB;

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
