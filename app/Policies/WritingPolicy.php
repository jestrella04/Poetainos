<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Auth\Access\HandlesAuthorization;

class WritingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Writing  $writing
     * @return mixed
     */
    public function update(User $user, Writing $writing)
    {
        if ($writing->author->is($user) || $user->isAllowed('admin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Writing  $writing
     * @return mixed
     */
    public function delete(User $user, Writing $writing)
    {
        if ($writing->author->is($user) || $user->isAllowed('admin')) {
            return true;
        }
    }
}
