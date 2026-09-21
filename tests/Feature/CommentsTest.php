<?php

use App\Models\BlockedUser;
use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

describe('the comments index', function (): void {
    it('excludes comments from authors the viewer has blocked', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $visibleAuthor = createUser();
        $blockedAuthor = createUser();
        $viewer = createUser();
        BlockedUser::factory()->create([
            'user_id' => $viewer->id,
            'blocked_user_id' => $blockedAuthor->id,
        ]);
        Comment::factory()->for($writing)->for($visibleAuthor, 'author')->create();
        Comment::factory()->for($writing)->for($blockedAuthor, 'author')->create();

        // When
        $response = actingAs($viewer)->getJson("/comments/{$writing->id}");

        // Then
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    });
});

describe('commenting', function (): void {
    it('notifies the writing author unless the commenter is the author', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $commenter = createUser();

        // When
        $response = actingAs($commenter)->post('/comments/create', [
            'comment' => 'Lovely piece!',
            'writing_id' => $writing->id,
        ]);

        // Then
        $response->assertOk();
        Notification::assertSentTo($author, WritingCommented::class);

        // Given
        Notification::fake();

        // When
        actingAs($author)->post('/comments/create', [
            'comment' => 'Thanks everyone!',
            'writing_id' => $writing->id,
        ]);

        // Then
        Notification::assertNotSentTo($author, WritingCommented::class);
    });

    it('notifies a mentioned user unless they are the author or the commenter', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $commenter = createUser();
        $mentioned = createUser(['username' => 'mentioned_user']);

        // When
        actingAs($commenter)->post('/comments/create', [
            'comment' => 'Great work @mentioned_user!',
            'writing_id' => $writing->id,
        ]);

        // Then
        Notification::assertSentTo($mentioned, WritingCommentMentioned::class);
        Notification::assertNotSentTo($author, WritingCommentMentioned::class);
        Notification::assertNotSentTo($commenter, WritingCommentMentioned::class);
    });
});

describe('deleting a comment', function (): void {
    it('allows the author to delete their comment but forbids another user', function (): void {
        // Given
        $author = createUser();
        $comment = Comment::factory()->for($author, 'author')->create();
        $other = createUser();

        // When
        $otherResponse = actingAs($other)->delete('/comments/delete/'.$comment->id);
        $authorResponse = actingAs($author)->delete('/comments/delete/'.$comment->id);

        // Then
        $otherResponse->assertForbidden();
        $authorResponse->assertOk();
        expect(Comment::find($comment->id))->toBeNull();
    });

    it('allows an admin to delete any comment', function (): void {
        // Given
        $comment = Comment::factory()->create();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->delete('/comments/delete/'.$comment->id);

        // Then
        $response->assertOk();
        expect(Comment::find($comment->id))->toBeNull();
    });
});

describe('mentions in a comment', function (): void {
    it('notify at most five different users', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();
        $commenter = createUser();
        $mentioned = User::factory()->count(8)->sequence(fn ($sequence) => ['username' => 'writer'.$sequence->index])->create();
        $message = $mentioned->map(fn (User $user): string => '@'.$user->username)->implode(' ');

        // When
        actingAs($commenter)->post('/comments/create', [
            'comment' => $message,
            'writing_id' => $writing->id,
        ])->assertOk();

        // Then
        Notification::assertSentTimes(WritingCommentMentioned::class, 5);
    });

    it('notify each user once however many times they are mentioned', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();
        $mentioned = createUser(['username' => 'mentioned_user']);

        // When
        actingAs(createUser())->post('/comments/create', [
            'comment' => '@mentioned_user @mentioned_user thanks',
            'writing_id' => $writing->id,
        ])->assertOk();

        // Then
        Notification::assertSentToTimes($mentioned, WritingCommentMentioned::class, 1);
    });

    it('ignore usernames that do not exist', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();

        // When
        actingAs(createUser())->post('/comments/create', [
            'comment' => '@nobody-here hello',
            'writing_id' => $writing->id,
        ])->assertOk();

        // Then
        Notification::assertNotSentTo($writing->author, WritingCommentMentioned::class);
    });
});
