<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Accounts can be edited by their owner and by admins.
     */
    public function update(User $user, User $model): bool
    {
        return $model->is($user) || $user->isAllowed('admin');
    }

    /**
     * Accounts can be deleted by whoever can edit them.
     */
    public function delete(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }
}
