<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Writing;

class WritingPolicy
{
    /**
     * Writings can be edited by their author and by admins.
     */
    public function update(User $user, Writing $writing): bool
    {
        return $writing->author?->is($user) === true || $user->isAllowed('admin');
    }

    /**
     * Writings can be deleted by whoever can edit them.
     */
    public function delete(User $user, Writing $writing): bool
    {
        return $this->update($user, $writing);
    }
}
