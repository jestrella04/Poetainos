<?php

use App\Models\Comment;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use App\Notifications\WritingLiked;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

test('a user can like and unlike a writing', function (): void {
    Notification::fake();

    $author = createUser();
    $writing = Writing::factory()->for($author, 'author')->create();
    $liker = createUser();

    actingAs($liker)->post("/likes/writing/{$writing->id}/store")
        ->assertJson(['method' => 'store', 'count' => 1]);

    Notification::assertSentTo($author, WritingLiked::class);

    actingAs($liker)->post("/likes/writing/{$writing->id}/store")
        ->assertJson(['method' => 'destroy', 'count' => 0]);
});

test('liking your own writing does not notify you', function (): void {
    Notification::fake();

    $author = createUser();
    $writing = Writing::factory()->for($author, 'author')->create();

    actingAs($author)->post("/likes/writing/{$writing->id}/store");

    Notification::assertNothingSent();
});

test('a user can like and unlike a comment', function (): void {
    Notification::fake();

    $author = createUser();
    $comment = Comment::factory()->for($author, 'author')->create();
    $liker = createUser();

    actingAs($liker)->post("/likes/comment/{$comment->id}/store")
        ->assertJson(['method' => 'store', 'count' => 1]);

    Notification::assertSentTo($author, CommentLiked::class);

    actingAs($liker)->post("/likes/comment/{$comment->id}/store")
        ->assertJson(['method' => 'destroy', 'count' => 0]);
});

test('deleting a like only removes the acting user\'s own like', function (): void {
    $writing = Writing::factory()->create();
    $liker = createUser();
    $otherLiker = createUser();

    actingAs($liker)->post("/likes/writing/{$writing->id}/store");
    actingAs($otherLiker)->post("/likes/writing/{$writing->id}/store");

    actingAs($liker)->delete("/likes/writing/{$writing->id}/delete")
        ->assertJson(['method' => 'destroy', 'count' => 1]);
});

test('liking a nonexistent writing 404s instead of crashing', function (): void {
    $liker = createUser();

    actingAs($liker)->post('/likes/writing/999999/store')->assertNotFound();
});

test('liking an unknown likeable type 404s instead of crashing', function (): void {
    $writing = Writing::factory()->create();
    $liker = createUser();

    actingAs($liker)->post("/likes/bogus/{$writing->id}/store")->assertNotFound();
});
