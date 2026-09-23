<?php

namespace Tests\Browser\Pages;

use App\Models\Comment;
use App\Models\Writing;
use Pest\Browser\Api\PendingAwaitablePage;

class WritingPage extends Page
{
    public const COMMENT_INPUT = '#comment-form-message';

    public const COMMENT_SUBMIT_BUTTON = '#comment-form-submit';

    private readonly WritingEntry $entry;

    public function __construct(PendingAwaitablePage $browser, Writing $writing)
    {
        parent::__construct($browser);

        $this->entry = new WritingEntry($writing);
    }

    public static function open(Writing $writing): self
    {
        return new self(static::visitUrl($writing->path()), $writing);
    }

    public static function commentMessage(Comment $comment): string
    {
        return "#comment-{$comment->id}-message";
    }

    public function like(): static
    {
        $this->browser->click($this->entry->likeButton());

        return $this;
    }

    public function shelve(): static
    {
        $this->browser->click($this->entry->shelveButton());

        return $this;
    }

    public function postComment(string $message): static
    {
        $this->browser->type(self::COMMENT_INPUT, $message)
            ->click(self::COMMENT_SUBMIT_BUTTON);

        return $this;
    }

    public function goToEditForm(): WritingFormPage
    {
        $this->browser->click($this->entry->moreActionsButton())
            ->click($this->entry->editLink());

        return new WritingFormPage($this->browser);
    }
}
