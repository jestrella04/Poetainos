<?php

use App\Models\BlockedUser;
use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

test('comments index excludes comments from authors the viewer has blocked', function (): void {
    $writing = Writing::factory()->create();
    $visibleAuthor = User::factory()->create();
    $blockedAuthor = User::factory()->create();
    $viewer = User::factory()->create();

    BlockedUser::factory()->create([
        'user_id' => $viewer->id,
        'blocked_user_id' => $blockedAuthor->id,
    ]);

    Comment::factory()->for($writing)->for($visibleAuthor, 'author')->create();
    Comment::factory()->for($writing)->for($blockedAuthor, 'author')->create();

    $response = actingAs($viewer)->getJson("/comments/{$writing->id}");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('commenting notifies the writing author unless the commenter is the author', function (): void {
    Notification::fake();

    $author = User::factory()->create();
    $writing = Writing::factory()->for($author, 'author')->create();
    $commenter = User::factory()->create();

    actingAs($commenter)->post('/comments/create', [
        'comment' => 'Lovely piece!',
        'writing_id' => $writing->id,
    ])->assertOk();

    Notification::assertSentTo($author, WritingCommented::class);

    Notification::fake();

    actingAs($author)->post('/comments/create', [
        'comment' => 'Thanks everyone!',
        'writing_id' => $writing->id,
    ]);

    Notification::assertNotSentTo($author, WritingCommented::class);
});

test('mentioning a user notifies them unless they are the author or the commenter', function (): void {
    Notification::fake();

    $author = User::factory()->create();
    $writing = Writing::factory()->for($author, 'author')->create();
    $commenter = User::factory()->create();
    $mentioned = User::factory()->create(['username' => 'mentioned_user']);

    actingAs($commenter)->post('/comments/create', [
        'comment' => 'Great work @mentioned_user!',
        'writing_id' => $writing->id,
    ]);

    Notification::assertSentTo($mentioned, WritingCommentMentioned::class);
    Notification::assertNotSentTo($author, WritingCommentMentioned::class);
    Notification::assertNotSentTo($commenter, WritingCommentMentioned::class);
});

test('the author can delete their comment but another user cannot', function (): void {
    $author = User::factory()->create();
    $comment = Comment::factory()->for($author, 'author')->create();
    $other = User::factory()->create();

    actingAs($other)->delete('/comments/delete/'.$comment->id)->assertForbidden();
    actingAs($author)->delete('/comments/delete/'.$comment->id)->assertOk();

    expect(Comment::find($comment->id))->toBeNull();
});

test('an admin can delete any comment', function (): void {
    $comment = Comment::factory()->create();
    $admin = actingAsAdmin();

    actingAs($admin)->delete('/comments/delete/'.$comment->id)->assertOk();

    expect(Comment::find($comment->id))->toBeNull();
});
