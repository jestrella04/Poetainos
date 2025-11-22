<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Complaint;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Models\Like;
use App\Models\Shelf;
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
                    'count' => User::all()->count(),
                ],
                'writings' => [
                    'title' => __('Writings'),
                    'count' => Writing::all()->count(),
                ],
                'comments' => [
                    'title' => __('Comments'),
                    'count' => Comment::all()->count(),
                ],
                'categories' => [
                    'title' => __('Categories'),
                    'count' => Category::all()->count(),
                ],
                'tags' => [
                    'title' => __('Tags'),
                    'count' => Tag::all()->count(),
                ],
                'likes' => [
                    'title' => __('Likes'),
                    'count' => Like::all()->count(),
                ],
                'shelves' => [
                    'title' => __('Bookmarks'),
                    'count' => Shelf::all()->count(),
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
            'total' => Category::all()->count(),
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
            'total' => Tag::all()->count(),
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
            'total' => User::all()->count(),
        ]);
    }

    public function writings()
    {
        $writings = Writing::select('id', 'user_id', 'title', 'slug', 'aura', 'created_at')
            ->with([
                'author' => function ($query) {
                    $query->select('id', 'username', 'name');
                }
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
            'total' => Writing::all()->count(),
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
            'total' => Page::all()->count(),
        ]);
    }

    public function tools()
    {
        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();

        return Inertia::render('admin/PoAdminTools', [
            'meta' => [
                'title' => getPageTitle([
                    __('Tools'),
                    __('Administration'),
                ]),
            ],
            'log' => shell_exec('tail -n 100 ' . $this->log),
            'info' => $pinfo,
        ]);
    }

    public function log()
    {
        header('Content-Description: Log download');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . basename($this->log) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($this->log));
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
            'total' => Complaint::all()->count(),
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
            'counter' => "https://counter.dev/dashboard.html?user=$user&token=$token%3D"
        ]);
    }
}
