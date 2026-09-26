<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Writing;
use Closure;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
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

    protected int $perPage;

    /** @var array<int, int>|null */
    private ?array $blockedUserIds = null;

    public function __construct()
    {
        $configured = (int) getSiteConfig('pagination');

        $this->perPage = $configured > 0 ? $configured : self::DEFAULT_PAGINATION;
    }

    /**
     * The ids of the authors the current user has blocked, looked up once per request.
     *
     * @return array<int, int>
     */
    protected function blockedAuthorIds(): array
    {
        $this->blockedUserIds ??= Auth::user()?->blockedAuthors()->pluck('blocked_user_id')->all() ?? [];

        return $this->blockedUserIds;
    }

    /**
     * A page of records: the raw page for JSON requests, the given Inertia
     * page otherwise. `$isDeferred` leaves the records out of the first
     * response so the page can request them with a partial reload; the query
     * only runs when they are actually sent.
     *
     * @template TPage of \Illuminate\Contracts\Pagination\Paginator
     *
     * @param  Closure(): TPage  $page
     * @param  array<string, mixed>  $props
     * @return Response|TPage
     */
    protected function paginatedPage(
        Closure $page,
        string $component,
        array $props,
        string $recordsProp,
        bool $isDeferred = true,
    ): Response|PaginatorContract {
        if (request()->expectsJson()) {
            return $page();
        }

        return Inertia::render($component, [
            ...$props,
            $recordsProp => $isDeferred ? Inertia::optional($page) : $page(),
        ]);
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
        return $this->paginatedPage(
            fn (): Paginator => $writings->simplePaginate($this->perPage)->withQueryString(),
            'writings/PoWritingsIndex',
            ['meta' => $meta, 'sort' => $sort, ...$extraProps],
            'writings',
            $isDeferred,
        );
    }

    /**
     * The currently authenticated user, aborting with a 401 if there is none.
     * Shared by every action that requires a logged-in user to proceed.
     */
    protected function requireAuthUser(): User
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        return $user;
    }
}
