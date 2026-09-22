<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\Shelf;
use App\Models\Writing;
use Tests\Browser\Pages\WritingPage;

use function Pest\Laravel\actingAs;

describe('interacting with a writing', function () {
    it('lets another user like a writing', function () {
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $reader = createUser();

        actingAs($reader);

        WritingPage::open($writing)
            ->like()
            ->browser()
            ->assertNoJavaScriptErrors();

        expect(Like::query()
            ->where('likeable_type', Writing::class)
            ->where('likeable_id', $writing->id)
            ->where('user_id', $reader->id)
            ->exists())->toBeTrue();
    });

    it('lets another user shelve a writing', function () {
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $reader = createUser();

        actingAs($reader);

        WritingPage::open($writing)
            ->shelve()
            ->browser()
            ->assertNoJavaScriptErrors();

        expect(Shelf::query()
            ->where('writing_id', $writing->id)
            ->where('user_id', $reader->id)
            ->exists())->toBeTrue();
    });

    it('lets another user post a comment', function () {
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $reader = createUser();

        actingAs($reader);

        // The comment form clears its input once the server accepts the comment.
        $browser = WritingPage::open($writing)
            ->postComment('What a lovely piece of writing!')
            ->browser()
            ->assertValue(WritingPage::COMMENT_INPUT, '')
            ->assertNoJavaScriptErrors();

        $comment = Comment::query()
            ->where('writing_id', $writing->id)
            ->where('user_id', $reader->id)
            ->where('message', 'What a lovely piece of writing!')
            ->firstOrFail();

        $browser->assertSeeIn(WritingPage::commentMessage($comment), 'What a lovely piece of writing!');
    });
});
