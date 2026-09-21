<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Writing;

class WritingOfTheDayPosted extends SocialPostNotification
{
    public function __construct(protected Writing $writing)
    {
        $this->message = __('":title" by :author is our #SelectionOfTheDay.', [
            'title' => $this->writing->title,
        ]).' '.__('Go read it, what are you waiting for? #poetry');
        $this->url = $this->writing->path();
    }

    protected function socialAuthor(): ?User
    {
        return $this->writing->author;
    }
}
