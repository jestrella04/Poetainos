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
use Inertia\Inertia;

class AdminController extends Controller
{
    private $log;

    public function __construct()
    {
        $this->log = storage_path('logs/laravel.log');
    }

    public function index()
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

    public function settings()
    {
        return Inertia::render('admin/PoAdminSettings', [
            'settings' => json_encode(Setting::where('name', 'site')->first()->pluck('data')[0], JSON_PRETTY_PRINT),
            'meta' => [
                'title' => getPageTitle([
                    __('Settings'),
                    __('Administration'),
                ]),
            ],
        ]);
    }

    public function categories()
    {
        if (request()->expectsJson()) {
            return Category::simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('admin/PoAdminCategories', [
            'meta' => [
                'title' => getPageTitle([
                    __('Categories'),
                    __('Administration'),
                ]),
            ],
            'total' => Category::count(),
        ]);
    }

    public function tags()
    {
        if (request()->expectsJson()) {
            return Tag::simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('admin/PoAdminTags', [
            'meta' => [
                'title' => getPageTitle([
                    __('Tags'),
                    __('Administration'),
                ]),
            ],
            'total' => Tag::count(),
        ]);
    }

    public function users()
    {
        $users = User::select('id', 'username', 'name', 'email', 'created_at', 'aura', 'karma');

        if (request()->expectsJson()) {
            return $users->simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('admin/PoAdminUsers', [
            'meta' => [
                'title' => getPageTitle([
                    __('Users'),
                    __('Administration'),
                ]),
            ],
            'total' => User::count(),
        ]);
    }

    public function writings()
    {
        $writings = Writing::select('id', 'user_id', 'title', 'slug', 'aura', 'created_at')
            ->with([
                'author' => function ($query): void {
                    $query->select('id', 'username', 'name');
                },
            ]);

        if (request()->expectsJson()) {
            return $writings->simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('admin/PoAdminWritings', [
            'meta' => [
                'title' => getPageTitle([
                    __('Writings'),
                    __('Administration'),
                ]),
            ],
            'total' => Writing::count(),
        ]);
    }

    public function pages()
    {
        if (request()->expectsJson()) {
            return Page::simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('admin/PoAdminPages', [
            'meta' => [
                'title' => getPageTitle([
                    __('Pages'),
                    __('Administration'),
                ]),
            ],
            'total' => Page::count(),
        ]);
    }

    public function tools()
    {
        return Inertia::render('admin/PoAdminTools', [
            'meta' => [
                'title' => getPageTitle([
                    __('Tools'),
                    __('Administration'),
                ]),
            ],
            'log' => tailFile($this->log, 100),
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

    public function log()
    {
        header('Content-Description: Log download');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="'.basename($this->log).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($this->log));
        readfile($this->log);
        exit;
    }

    public function complaints()
    {
        if (request()->expectsJson()) {
            return Complaint::simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('admin/PoAdminComplaints', [
            'meta' => [
                'title' => getPageTitle([
                    __('Complaints'),
                    __('Administration'),
                ]),
            ],
            'total' => Complaint::count(),
        ]);
    }

    public function websockets()
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

    public function analytics()
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
}
