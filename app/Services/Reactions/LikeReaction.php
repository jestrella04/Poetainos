<?php

namespace App\Services\Reactions;

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use App\Notifications\WritingLiked;
use Illuminate\Notifications\Notification;

class LikeReaction implements Reaction
{
    public function __construct(private Writing|Comment $target) {}

    public function isActive(User $actor): bool
    {
        return $this->target->likes()->where('user_id', $actor->id)->exists();
    }

    public function add(User $actor): void
    {
        $this->target->likes()->create(['user_id' => $actor->id, 'vote' => 1]);
    }

    public function remove(User $actor): void
    {
        $this->target->likes()->where('user_id', $actor->id)->delete();
    }

    public function count(): int
    {
        return $this->target->likes()->count();
    }

    public function writing(): ?Writing
    {
        return $this->target instanceof Writing ? $this->target : null;
    }

    public function author(): ?User
    {
        return $this->target->author;
    }

    public function notification(User $actor): Notification
    {
        return $this->target instanceof Writing
            ? new WritingLiked($this->target, $actor)
            : new CommentLiked($this->target, $actor);
    }
}
