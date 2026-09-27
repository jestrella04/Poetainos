<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
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
     * Block another user, then return to the page they were blocked from.
     */
    public function store(User $user): RedirectResponse
    {
        $authUser = $this->requireAuthUser();

        abort_if($authUser->is($user), 422, __('You cannot block yourself.'));

        $authUser->block($user);

        Inertia::flash(['message' => 'users.user-blocked', 'color' => 'success']);

        return back();
    }

    /**
     * Unblock a previously blocked user, then return to where it was done.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->requireAuthUser()->unblock($user);

        Inertia::flash(['message' => 'users.user-unblocked', 'color' => 'success']);

        return back();
    }
}
