<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected ?int $pagination = null;

    public function __construct()
    {
        $this->pagination = (int) getSiteConfig('pagination');
    }

    /**
     * @return array<int, int>
     */
    public function getBlockedUsers(): array
    {
        $user = auth()->user();

        return $user !== null
            ? $user->blockedAuthors()->pluck('blocked_user_id')->toArray()
            : [0];
    }

    /**
     * The currently authenticated user, aborting with a 401 if there is none.
     * Shared by every action that requires a logged-in user to proceed.
     */
    public function requireAuthUser(): User
    {
        $user = auth()->user();

        if ($user === null) {
            abort(401);
        }

        return $user;
    }
}
