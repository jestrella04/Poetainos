<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Contracts\Queue\ShouldQueue;

class WritingPublished extends SocialPostNotification implements ShouldQueue
{
    public function __construct(protected Writing $writing)
    {
        $this->message = __('":title" by :author has just been published on our site.', [
            'title' => $this->writing->title,
        ]).' '.__('Go read it, what are you waiting for? #poetry');
        $this->url = $this->writing->path();
    }

    protected function socialAuthor(): ?User
    {
        return $this->writing->author;
    }
}
