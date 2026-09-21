<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private const DEFAULT_PAGINATION = 15;

    private const RECENT_VIEWS_REMEMBERED = 100;

    protected int $pagination;

    /** @var array<int, int>|null */
    private ?array $blockedUserIds = null;

    public function __construct()
    {
        $configured = (int) getSiteConfig('pagination');

        $this->pagination = $configured > 0 ? $configured : self::DEFAULT_PAGINATION;
    }

    /**
     * The ids of the authors the current user has blocked, looked up once per request.
     *
     * @return array<int, int>
     */
    protected function getBlockedUsers(): array
    {
        $this->blockedUserIds ??= Auth::user()?->blockedAuthors()->pluck('blocked_user_id')->all() ?? [];

        return $this->blockedUserIds;
    }

    /**
     * A page of writings: the raw page for JSON requests, the shared writings
     * index otherwise. `$isDeferred` leaves the writings out of the first
     * response so the page can request them with a partial reload.
     *
     * @param  Builder<Writing>|Relation<Writing, *, *>  $writings
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $extraProps
     * @return Response|Paginator<int, Writing>
     */
    protected function writingsIndex(
        Builder|Relation $writings,
        string $sort,
        array $meta,
        array $extraProps = [],
        bool $isDeferred = true,
    ): Response|Paginator {
        $page = fn (): Paginator => $writings->simplePaginate($this->pagination)->withQueryString();

        if (request()->expectsJson()) {
            return $page();
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => $meta,
            'writings' => $isDeferred ? Inertia::optional($page) : $page(),
            'sort' => $sort,
            ...$extraProps,
        ]);
    }

    /**
     * Count a view of the model once per visitor session, so refreshing the page doesn't inflate it.
     */
    protected function countViewOnce(User|Writing $viewed): void
    {
        $viewKey = $viewed->getTable().':'.$viewed->getKey();
        $recentViews = session()->get('recent_views', []);

        if (in_array($viewKey, $recentViews, true)) {
            return;
        }

        $viewed->incrementViews();

        session()->put('recent_views', array_slice([...$recentViews, $viewKey], -self::RECENT_VIEWS_REMEMBERED));
    }

    /**
     * The currently authenticated user, aborting with a 401 if there is none.
     * Shared by every action that requires a logged-in user to proceed.
     */
    public function requireAuthUser(): User
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        return $user;
    }
}
