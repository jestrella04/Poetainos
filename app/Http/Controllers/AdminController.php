<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Complaint;
use App\Models\Like;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Shelf;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use App\Services\ActivityFeed;
use App\Services\LogReader;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/PoAdminIndex', [
            'counters' => [
                'users' => [
                    'title' => __('Users'),
                    'count' => User::count(),
                ],
                'writings' => [
                    'title' => __('Writings'),
                    'count' => Writing::count(),
                ],
                'comments' => [
                    'title' => __('Comments'),
                    'count' => Comment::count(),
                ],
                'categories' => [
                    'title' => __('Categories'),
                    'count' => Category::count(),
                ],
                'tags' => [
                    'title' => __('Tags'),
                    'count' => Tag::count(),
                ],
                'likes' => [
                    'title' => __('Likes'),
                    'count' => Like::count(),
                ],
                'shelves' => [
                    'title' => __('Bookmarks'),
                    'count' => Shelf::count(),
                ],
            ],
            'meta' => [
                'title' => getPageTitle([
                    __('Administration'),
                ]),
            ],
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('admin/PoAdminSettings', [
            'settings' => json_encode(Setting::where('name', 'site')->value('data'), JSON_PRETTY_PRINT),
            'meta' => [
                'title' => getPageTitle([
                    __('Settings'),
                    __('Administration'),
                ]),
            ],
        ]);
    }

    /**
     * @return Response|Paginator<int, Category>
     */
    public function categories(): Response|Paginator
    {
        return $this->listing('admin/PoAdminCategories', __('Categories'), Category::query());
    }

    /**
     * @return Response|Paginator<int, Tag>
     */
    public function tags(): Response|Paginator
    {
        return $this->listing('admin/PoAdminTags', __('Tags'), Tag::query());
    }

    /**
     * @return Response|Paginator<int, User>
     */
    public function users(): Response|Paginator
    {
        return $this->listing(
            'admin/PoAdminUsers',
            __('Users'),
            User::select('id', 'username', 'name', 'email', 'created_at', 'aura', 'karma'),
        );
    }

    /**
     * @return Response|Paginator<int, Writing>
     */
    public function writings(): Response|Paginator
    {
        return $this->listing(
            'admin/PoAdminWritings',
            __('Writings'),
            Writing::select('id', 'user_id', 'title', 'slug', 'aura', 'created_at')
                ->with(['author' => fn ($query) => $query->select('id', 'username', 'name')]),
        );
    }

    /**
     * @return Response|Paginator<int, Page>
     */
    public function pages(): Response|Paginator
    {
        return $this->listing('admin/PoAdminPages', __('Pages'), Page::query());
    }

    /**
     * @return Response|Paginator<int, array{kind: string, subject_id: int, created_at: Carbon, user: User|null, writing: Writing|null}>
     */
    public function activity(ActivityFeed $feed): Response|Paginator
    {
        return $this->paginatedPage(
            fn (): Paginator => $feed->page($this->perPage),
            'admin/PoAdminActivity',
            [
                'meta' => [
                    'title' => getPageTitle([__('Activity'), __('Administration')]),
                ],
                'total' => fn (): int => $feed->count(),
            ],
            recordsProp: null,
        );
    }

    public function logs(LogReader $reader): Response
    {
        return Inertia::render('admin/PoAdminLogs', [
            'meta' => [
                'title' => getPageTitle([
                    __('Logs'),
                    __('Administration'),
                ]),
            ],
            'files' => $reader->files(),
        ]);
    }

    /**
     * One page of a log's entries, newest first.
     *
     * @return array{entries: list<array{level: string|null, environment: string|null, date: string|null, message: string, details: string}>, before: int|null}
     */
    public function logEntries(LogReader $reader): array
    {
        request()->validate([
            'file' => ['required', 'string'],
            'before' => ['nullable', 'integer', 'min:0'],
            'level' => ['nullable', Rule::in(LogReader::LEVELS)],
            'search' => ['nullable', 'string', 'max:200'],
        ]);

        return $reader->entries(
            $this->logPath($reader, request('file')),
            request('before') === null ? null : (int) request('before'),
            request('level'),
            request('search'),
        );
    }

    public function downloadLog(LogReader $reader, string $file): BinaryFileResponse
    {
        return response()->download($this->logPath($reader, $file), $file, ['Content-Type' => 'text/plain']);
    }

    public function clearLog(LogReader $reader, string $file): RedirectResponse
    {
        $reader->clear($this->logPath($reader, $file));

        Inertia::flash(['message' => 'admin.log-cleared', 'color' => 'success']);

        return back();
    }

    /**
     * @return Response|Paginator<int, Complaint>
     */
    public function complaints(): Response|Paginator
    {
        return $this->listing('admin/PoAdminComplaints', __('Complaints'), Complaint::query());
    }

    public function analytics(): Response
    {
        $user = config('services.counter.user_id');
        $token = config('services.counter.access_token');

        return Inertia::render('admin/PoAdminAnalytics', [
            'meta' => [
                'title' => getPageTitle([
                    __('Analytics'),
                    __('Administration'),
                ]),
            ],
            'counter' => 'https://counter.dev/dashboard.html?'.http_build_query([
                'user' => $user,
                'token' => $token,
            ]),
        ]);
    }

    /**
     * An admin table: one page of rows for JSON requests, the table's page otherwise.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $rows
     * @return Response|Paginator<int, TModel>
     */
    private function listing(string $component, string $title, Builder $rows): Response|Paginator
    {
        return $this->paginatedPage(
            fn (): Paginator => $rows->simplePaginate($this->perPage)->withQueryString(),
            $component,
            [
                'meta' => [
                    'title' => getPageTitle([$title, __('Administration')]),
                ],
                'total' => fn (): int => $rows->count(),
            ],
            recordsProp: null,
        );
    }

    /**
     * The path of a listed log file; anything else is not found.
     */
    private function logPath(LogReader $reader, string $file): string
    {
        $path = $reader->path($file);

        abort_if($path === null, 404);

        return $path;
    }
}
