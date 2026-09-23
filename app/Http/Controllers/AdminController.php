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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Inertia\Inertia;
use Inertia\Response;
use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    private const LOG_LINES_SHOWN = 100;

    private string $log;

    public function __construct()
    {
        parent::__construct();

        $this->log = storage_path('logs/laravel.log');
    }

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

    public function tools(): Response
    {
        return Inertia::render('admin/PoAdminTools', [
            'meta' => [
                'title' => getPageTitle([
                    __('Tools'),
                    __('Administration'),
                ]),
            ],
            'log' => $this->tailLog(),
            'info' => [
                __('PHP version') => PHP_VERSION,
                __('Laravel version') => app()->version(),
                __('Memory limit') => ini_get('memory_limit'),
                __('Upload max filesize') => ini_get('upload_max_filesize'),
                __('Post max size') => ini_get('post_max_size'),
                __('Max execution time') => ini_get('max_execution_time').'s',
                __('OPcache enabled') => function_exists('opcache_get_status') && opcache_get_status() !== false ? __('Yes') : __('No'),
                __('Loaded extensions') => implode(', ', get_loaded_extensions()),
            ],
        ]);
    }

    /**
     * Download the full application log; an empty file when nothing has been logged yet.
     */
    public function log(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            if (is_readable($this->log) === true) {
                readfile($this->log);
            }
        }, 'laravel.log', ['Content-Type' => 'text/plain']);
    }

    /**
     * @return Response|Paginator<int, Complaint>
     */
    public function complaints(): Response|Paginator
    {
        return $this->listing('admin/PoAdminComplaints', __('Complaints'), Complaint::query());
    }

    public function websockets(): Response
    {
        return Inertia::render('admin/PoAdminWebsockets', [
            'meta' => [
                'title' => getPageTitle([
                    __('Websockets'),
                    __('Administration'),
                ]),
            ],
        ]);
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
        if (request()->expectsJson()) {
            return $rows->simplePaginate($this->perPage)->withQueryString();
        }

        return Inertia::render($component, [
            'meta' => [
                'title' => getPageTitle([$title, __('Administration')]),
            ],
            'total' => $rows->count(),
        ]);
    }

    /**
     * The last lines of the application log, or nothing when it can't be read.
     */
    private function tailLog(): string
    {
        if (! is_readable($this->log)) {
            return '';
        }

        $file = new SplFileObject($this->log, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();

        $file->seek(max(0, $lastLine - self::LOG_LINES_SHOWN));

        $tail = [];

        while (! $file->eof()) {
            $tail[] = $file->fgets();
            $file->next();
        }

        return implode('', $tail);
    }
}
