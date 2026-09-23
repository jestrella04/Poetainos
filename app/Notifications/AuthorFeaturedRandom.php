<?php

namespace App\Notifications;

use App\Models\User;

class AuthorFeaturedRandom extends SocialPostNotification
{
    public function __construct(protected User $author)
    {
        $this->message = __(':author is one of our most prominent authors.')
            .' '.__('You are invited to discover all the magic present in their writings. #poetry');
        $this->url = $this->author->writingsPath();
    }

    protected function socialAuthor(): ?User
    {
        return $this->author;
    }
}
