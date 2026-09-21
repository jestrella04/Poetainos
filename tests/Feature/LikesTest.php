<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use App\Notifications\WritingLiked;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

describe('liking a writing', function (): void {
    it('allows a user to like and unlike a writing', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $liker = createUser();

        // When
        $likeResponse = actingAs($liker)->post("/likes/writing/{$writing->id}/store");

        // Then
        $likeResponse->assertJson(['method' => 'store', 'count' => 1]);
        Notification::assertSentTo($author, WritingLiked::class);

        // When
        $unlikeResponse = actingAs($liker)->post("/likes/writing/{$writing->id}/store");

        // Then
        $unlikeResponse->assertJson(['method' => 'destroy', 'count' => 0]);
    });

    it('does not notify the author when they like their own writing', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();

        // When
        actingAs($author)->post("/likes/writing/{$writing->id}/store");

        // Then
        Notification::assertNothingSent();
    });

    it('only removes the acting user\'s own like when they unlike', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $liker = createUser();
        $otherLiker = createUser();
        actingAs($liker)->post("/likes/writing/{$writing->id}/store");
        actingAs($otherLiker)->post("/likes/writing/{$writing->id}/store");

        // When
        $response = actingAs($liker)->post("/likes/writing/{$writing->id}/store");

        // Then
        $response->assertJson(['method' => 'destroy', 'count' => 1]);
    });

    it('404s instead of crashing for a nonexistent writing', function (): void {
        // Given
        $liker = createUser();

        // When
        $response = actingAs($liker)->post('/likes/writing/999999/store');

        // Then
        $response->assertNotFound();
    });

    it('404s instead of crashing for an unknown likeable type', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $liker = createUser();

        // When
        $response = actingAs($liker)->post("/likes/bogus/{$writing->id}/store");

        // Then
        $response->assertNotFound();
    });
});

describe('liking a comment', function (): void {
    it('allows a user to like and unlike a comment', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $comment = Comment::factory()->for($author, 'author')->create();
        $liker = createUser();

        // When
        $likeResponse = actingAs($liker)->post("/likes/comment/{$comment->id}/store");

        // Then
        $likeResponse->assertJson(['method' => 'store', 'count' => 1]);
        Notification::assertSentTo($author, CommentLiked::class);

        // When
        $unlikeResponse = actingAs($liker)->post("/likes/comment/{$comment->id}/store");

        // Then
        $unlikeResponse->assertJson(['method' => 'destroy', 'count' => 0]);
    });
});

describe('a double click on the like button', function (): void {
    it('reports the like instead of failing when it was already created', function (): void {
        // Given
        $writing = Writing::factory()->create();
        Like::creating(fn () => throw new UniqueConstraintViolationException('sqlite', 'insert', [], new Exception('duplicate')));

        // When
        $response = actingAs(createUser())->post("/likes/writing/{$writing->id}/store");

        // Then
        $response->assertOk()->assertJson(['method' => 'store']);
    });
});
