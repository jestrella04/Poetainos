<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Comments can be deleted by their author and by admins.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id || $user->isAllowed('admin');
    }
}
