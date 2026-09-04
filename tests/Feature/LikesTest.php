<?php

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use App\Notifications\WritingLiked;
use Illuminate\Support\Facades\Notification;

test('a user can like and unlike a writing', function (): void {
    Notification::fake();

    $author = User::factory()->create();
    $writing = Writing::factory()->for($author, 'author')->create();
    $liker = User::factory()->create();

    $this->actingAs($liker)->post("/likes/writing/{$writing->id}/store")
        ->assertJson(['method' => 'store', 'count' => 1]);

    Notification::assertSentTo($author, WritingLiked::class);

    $this->actingAs($liker)->post("/likes/writing/{$writing->id}/store")
        ->assertJson(['method' => 'destroy', 'count' => 0]);
});

test('liking your own writing does not notify you', function (): void {
    Notification::fake();

    $author = User::factory()->create();
    $writing = Writing::factory()->for($author, 'author')->create();

    $this->actingAs($author)->post("/likes/writing/{$writing->id}/store");

    Notification::assertNothingSent();
});

test('a user can like and unlike a comment', function (): void {
    Notification::fake();

    $author = User::factory()->create();
    $comment = Comment::factory()->for($author, 'author')->create();
    $liker = User::factory()->create();

    $this->actingAs($liker)->post("/likes/comment/{$comment->id}/store")
        ->assertJson(['method' => 'store', 'count' => 1]);

    Notification::assertSentTo($author, CommentLiked::class);

    $this->actingAs($liker)->post("/likes/comment/{$comment->id}/store")
        ->assertJson(['method' => 'destroy', 'count' => 0]);
});

test('deleting a like only removes the acting user\'s own like', function (): void {
    $writing = Writing::factory()->create();
    $liker = User::factory()->create();
    $otherLiker = User::factory()->create();

    $this->actingAs($liker)->post("/likes/writing/{$writing->id}/store");
    $this->actingAs($otherLiker)->post("/likes/writing/{$writing->id}/store");

    $this->actingAs($liker)->delete("/likes/writing/{$writing->id}/delete")
        ->assertJson(['method' => 'destroy', 'count' => 1]);
});
