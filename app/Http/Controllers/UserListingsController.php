<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Pagination\Paginator;
use Inertia\Response;

/**
 * The writings listed under a user's profile: the ones they wrote, shelved and liked.
 */
class UserListingsController extends Controller
{
    /**
     * @return Response|Paginator<int, Writing>
     */
    public function writings(User $user): Response|Paginator
    {
        return $this->writingsIndex(
            $user->writings(),
            ['title' => getPageTitle([__('Writings'), $user->getName()]), 'canonical' => $user->writingsPath()],
        );
    }

    /**
     * @return Response|Paginator<int, Writing>
     */
    public function shelf(User $user): Response|Paginator
    {
        return $this->writingsIndex(
            Writing::whereIn('id', $user->shelf()->select('writings.id')),
            ['title' => getPageTitle([__('Shelf'), $user->getName()]), 'canonical' => route('users.shelf.index', $user)],
        );
    }

    /**
     * The writings the user liked, other than their own.
     *
     * @return Response|Paginator<int, Writing>
     */
    public function likes(User $user): Response|Paginator
    {
        return $this->writingsIndex(
            Writing::whereIn('id', $user->likedWritingIds())->whereNot('user_id', $user->id),
            ['title' => getPageTitle([__('Likes'), $user->getName()]), 'canonical' => route('users.likes.index', $user)],
        );
    }
}
