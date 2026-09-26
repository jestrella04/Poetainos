<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Inertia\Response;

/**
 * The authors the signed-in user blocked, whose content is hidden from them.
 */
class BlockedUsersController extends Controller
{
    /**
     * The authenticated user's blocked authors.
     *
     * @return Response|Paginator<int, User>
     */
    public function index(): Response|Paginator
    {
        $blockedUsers = User::forAuthorSummary()
            ->whereIn('id', $this->requireAuthUser()->blockedAuthors()->select('blocked_user_id'));

        return $this->paginatedPage(
            fn (): Paginator => $blockedUsers->simplePaginate($this->perPage)->withQueryString(),
            'users/PoUsersBlockedIndex',
            [
                'meta' => [
                    'title' => getPageTitle([__('Blocked authors'), __('My account')]),
                ],
            ],
            'blockedUsers',
        );
    }

    /**
     * Block another user.
     *
     * @return array<int, mixed>
     */
    public function store(User $user): array
    {
        $authUser = $this->requireAuthUser();

        abort_if($authUser->is($user), 422, __('You cannot block yourself.'));

        $authUser->block($user);

        return [];
    }

    /**
     * Unblock a previously blocked user.
     *
     * @return array<int, mixed>
     */
    public function destroy(User $user): array
    {
        $this->requireAuthUser()->unblock($user);

        return [];
    }
}
