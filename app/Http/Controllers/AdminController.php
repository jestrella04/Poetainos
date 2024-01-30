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
use App\Models\Writing;
use Inertia\Inertia;

class AdminController extends Controller
{
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
        $params = [
            'title' => getPageTitle([
                __('Categories'),
                __('Administration'),
            ]),
        ];

        return view('admin.categories', [
            'categories' => Category::simplePaginate($this->pagination),
            'params' => $params,
        ]);
    }

    public function tags()
    {
        $params = [
            'title' => getPageTitle([
                __('Tags'),
                __('Administration'),
            ]),
        ];

        return view('admin.tags', [
            'tags' => Tag::simplePaginate($this->pagination),
            'params' => $params,
        ]);
    }

    public function users()
    {
        $params = [
            'title' => getPageTitle([
                __('Users'),
                __('Administration'),
            ]),
        ];

        return view('admin.users', [
            'users' => User::simplePaginate($this->pagination),
            'params' => $params,
        ]);
    }

    public function writings()
    {
        $writings = Writing::select('id', 'user_id', 'title', 'slug', 'aura', 'created_at')
            ->with(['author' => function ($query) {
                $query->select('id', 'username', 'name');
            }]);

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
        $params = [
            'title' => getPageTitle([
                __('Pages'),
                __('Administration'),
            ]),
        ];

        return view('admin.pages', [
            'pages' => Page::simplePaginate($this->pagination),
            'params' => $params,
        ]);
    }

    public function tools()
    {
        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();

        $log = storage_path('logs/laravel.log');

        return Inertia::render('admin/PoAdminTools', [
            'meta' => [
                'title' => getPageTitle([
                    __('Tools'),
                    __('Administration'),
                ]),
            ],
            'log' => shell_exec('tail -n 100 ' . $log),
            'info' => $pinfo,
        ]);
    }

    public function complaints()
    {
        $params = [
            'title' => getPageTitle([
                __('Complaints'),
                __('Administration'),
            ]),
        ];

        return view('admin.complaints', [
            'complaints' => Complaint::simplePaginate($this->pagination),
            'params' => $params,
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
