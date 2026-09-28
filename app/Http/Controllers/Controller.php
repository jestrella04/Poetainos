<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Writing;
use App\Services\ImageStorage;
use Closure;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private const DEFAULT_PAGINATION = 15;

    /**
     * The orders every writings listing can be sorted by, the first being the default.
     *
     * @var array<int, string>
     */
    private const WRITING_SORTS = ['latest', 'popular', 'likes'];

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
     * only runs when they are actually sent. Without a `$recordsProp` the page
     * gets no records at all and fetches every page as JSON (the admin tables).
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
        ?string $recordsProp,
        bool $isDeferred = true,
    ): Response|PaginatorContract {
        if (request()->expectsJson()) {
            return $page();
        }

        if ($recordsProp === null) {
            return Inertia::render($component, $props);
        }

        return Inertia::render($component, [
            ...$props,
            $recordsProp => $isDeferred ? Inertia::optional($page) : $page(),
        ]);
    }

    /**
     * A page of the given writings, the viewer's blocked authors left out,
     * sorted by the requested order: the raw page for JSON requests, the
     * shared writings index otherwise. `$isDeferred` leaves the writings out
     * of the first response so the page can request them with a partial reload.
     *
     * @param  Builder<Writing>|Relation<Writing, *, *>  $writings
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $extraProps
     * @return Response|Paginator<int, Writing>
     */
    protected function writingsIndex(
        Builder|Relation $writings,
        array $meta,
        array $extraProps = [],
        bool $isDeferred = true,
    ): Response|Paginator {
        $sort = resolveSort(self::WRITING_SORTS, self::WRITING_SORTS[0]);

        return $this->paginatedPage(
            fn (): Paginator => $writings->visibleTo($this->blockedAuthorIds())
                ->withListingRelations()
                ->sorted($sort)
                ->simplePaginate($this->perPage)
                ->withQueryString(),
            'writings/PoWritingsIndex',
            ['meta' => $meta, 'sort' => $sort, ...$extraProps],
            'writings',
            $isDeferred,
        );
    }

    /**
     * The validation rule of an uploaded avatar or cover image.
     */
    protected function imageUploadRule(): string
    {
        return 'nullable|file|mimes:jpg,jpeg,png,webp|max:'.getSiteConfig('uploads_max_file_size')
            .'|dimensions:max_width='.ImageStorage::MAX_DIMENSION.',max_height='.ImageStorage::MAX_DIMENSION;
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
