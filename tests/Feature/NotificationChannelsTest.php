<?php

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use App\Notifications\WritingFeatured;
use App\Notifications\WritingLiked;

describe('notification emails', function (): void {
    /**
     * @return array<string, array{0: Closure(User, Writing): object}>
     */
    $notifications = [
        'a new comment' => [fn (User $commenter, Writing $writing) => new WritingCommented($writing, $commenter)],
        'a mention' => [fn (User $commenter, Writing $writing) => new WritingCommentMentioned(Comment::factory()->for($writing)->create(), $commenter)],
        'a featured writing' => [fn (User $commenter, Writing $writing) => new WritingFeatured($writing)],
    ];

    it('are sent to users who never chose', function (Closure $makeNotification): void {
        // Given
        $recipient = createUser(['extra_info' => null]);
        $notification = $makeNotification(createUser(), Writing::factory()->for($recipient, 'author')->create());

        // Then
        expect($notification->via($recipient))->toContain('mail');
    })->with($notifications);

    it('are sent to users who opted in', function (Closure $makeNotification): void {
        // Given
        $recipient = createUser(['extra_info' => ['notifications' => ['email' => 'on']]]);
        $notification = $makeNotification(createUser(), Writing::factory()->for($recipient, 'author')->create());

        // Then
        expect($notification->via($recipient))->toContain('mail');
    })->with($notifications);

    it('are not sent to users who opted out, but the in-app notification still is', function (Closure $makeNotification): void {
        // Given
        $recipient = createUser(['extra_info' => ['notifications' => ['email' => 'off']]]);
        $notification = $makeNotification(createUser(), Writing::factory()->for($recipient, 'author')->create());

        // Then
        expect($notification->via($recipient))->not->toContain('mail')->toContain('database');
    })->with($notifications);
});

describe('the content of "someone did something on your writing" notifications', function (): void {
    it('names who did it, the site, and links to the writing', function (string $class, string $expectedAction): void {
        // Given
        $actor = createUser(['name' => 'Emily Dickinson']);
        $recipient = createUser();
        $writing = Writing::factory()->for($recipient, 'author')->create();
        $target = $class === CommentLiked::class ? Comment::factory()->for($writing)->create() : $writing;
        $notification = new $class($target, $actor);

        // When
        $message = $notification->toWebPush($recipient, null)->toArray();
        $mail = $notification->toMail($recipient);

        // Then
        expect($message['title'])->toContain('Emily Dickinson')->toContain('Poetainos');
        expect($message['body'])->toContain('Emily Dickinson')->toContain('Poetainos');
        expect($mail->actionText)->toBe(__($expectedAction));
        expect($mail->actionUrl)->toBe($writing->path());
        expect($mail->greeting)->toBe(__('Hello!'));
    })->with([
        'a like on a writing' => [WritingLiked::class, 'View writing'],
        'a like on a comment' => [CommentLiked::class, 'View comment'],
        'a new comment' => [WritingCommented::class, 'View writing'],
    ]);

    it('links a mention to the comment itself', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $comment = Comment::factory()->for($writing)->create();
        $notification = new WritingCommentMentioned($comment, createUser());

        // When
        $mail = $notification->toMail(createUser());

        // Then
        expect($mail->actionUrl)->toBe($writing->path().'#comment-'.$comment->id);
    });
});
