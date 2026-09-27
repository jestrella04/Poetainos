<?php

use App\Models\BlockedUser;
use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

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
        $response = actingAs($viewer)->getJson(route('comments.index', $writing));

        // Then
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    });

    it('marks the comments the viewer liked', function (): void {
        // Given
        $viewer = createUser();
        $writing = Writing::factory()->create();
        $liked = Comment::factory()->for($writing)->create();
        $other = Comment::factory()->for($writing)->create();
        $liked->likes()->create(['user_id' => $viewer->id, 'vote' => 1]);

        // When
        $comments = collect((array) actingAs($viewer)->getJson(route('comments.index', $writing))->json('data'))->keyBy('id');

        // Then
        expect($comments[$liked->id]['is_liked'])->toBeTrue();
        expect($comments[$other->id]['is_liked'])->toBeFalse();
    });

    it('pages through every comment of a writing', function (): void {
        // Given
        $perPage = getSiteConfig('pagination');
        $writing = Writing::factory()->create();
        Comment::factory()->for($writing)->count($perPage + 1)->create();

        // When
        $firstPage = getJson(route('comments.index', $writing));
        $secondPage = getJson($firstPage->json('next_page_url'));

        // Then
        $firstPage->assertJsonCount($perPage, 'data');
        $secondPage->assertJsonCount(1, 'data');
        expect($secondPage->json('next_page_url'))->toBeNull();
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
            'comment' => fake()->sentence(),
            'writing_id' => $writing->id,
        ]);

        // Then
        $response->assertCreated();
        Notification::assertSentTo($author, WritingCommented::class);

        // Given
        Notification::fake();

        // When
        actingAs($author)->post('/comments/create', [
            'comment' => fake()->sentence(),
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
        $mentioned = createUser(['username' => fakeUsername()]);

        // When
        actingAs($commenter)->post('/comments/create', [
            'comment' => fake()->sentence()." @{$mentioned->username}!",
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
        $authorResponse->assertRedirect();
        expect(Comment::find($comment->id))->toBeNull();
    });

    it('allows an admin to delete any comment', function (): void {
        // Given
        $comment = Comment::factory()->create();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->delete('/comments/delete/'.$comment->id);

        // Then
        $response->assertRedirect();
        expect(Comment::find($comment->id))->toBeNull();
    });
});

describe('mentions in a comment', function (): void {
    it('notify at most five different users', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();
        $commenter = createUser();
        $mentioned = User::factory()->count(8)->sequence(fn (): array => ['username' => fakeUsername()])->create();
        $message = $mentioned->map(fn (User $user): string => '@'.$user->username)->implode(' ');

        // When
        actingAs($commenter)->post('/comments/create', [
            'comment' => $message,
            'writing_id' => $writing->id,
        ])->assertCreated();

        // Then
        Notification::assertSentTimes(WritingCommentMentioned::class, 5);
    });

    it('notify a username with a dot, even when the mention ends a sentence', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();
        $mentioned = createUser(['username' => fakeUsername().'.'.fakeUsername()]);

        // When
        actingAs(createUser())->post('/comments/create', [
            'comment' => fake()->sentence()." @{$mentioned->username}.",
            'writing_id' => $writing->id,
        ])->assertCreated();

        // Then
        Notification::assertSentTo($mentioned, WritingCommentMentioned::class);
    });

    it('do not notify a user who blocked the commenter', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();
        $commenter = createUser();
        $mentioned = createUser(['username' => fakeUsername()]);
        $mentioned->block($commenter);

        // When
        actingAs($commenter)->post('/comments/create', [
            'comment' => "@{$mentioned->username} ".fake()->sentence(),
            'writing_id' => $writing->id,
        ])->assertCreated();

        // Then
        Notification::assertNotSentTo($mentioned, WritingCommentMentioned::class);
    });

    it('notify each user once however many times they are mentioned', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();
        $mentioned = createUser(['username' => fakeUsername()]);

        // When
        actingAs(createUser())->post('/comments/create', [
            'comment' => "@{$mentioned->username} @{$mentioned->username} ".fake()->sentence(),
            'writing_id' => $writing->id,
        ])->assertCreated();

        // Then
        Notification::assertSentToTimes($mentioned, WritingCommentMentioned::class, 1);
    });

    it('ignore usernames that do not exist', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();

        // When
        actingAs(createUser())->post('/comments/create', [
            'comment' => '@'.fakeUsername().' '.fake()->sentence(),
            'writing_id' => $writing->id,
        ])->assertCreated();

        // Then
        Notification::assertNotSentTo($writing->author, WritingCommentMentioned::class);
    });
});
