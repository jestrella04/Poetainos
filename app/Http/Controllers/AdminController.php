<?php

namespace App\Http\Controllers;

use App\Category;
use App\Comment;
use App\Complaint;
use App\Page;
use App\Reply;
use App\Setting;
use App\Tag;
use App\User;
use App\Vote;
use App\Writing;

class AdminController extends Controller
{
    public function index() {
        $params = [
            'title' => getPageTitle([
                __('Administration'),
            ]),
        ];

        return view('admin.summary', [
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
                    'count' => Comment::all()->count() + Reply::all()->count(),
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
                    'count' => Vote::all()->count(),
                ],
            ],
            'params' => $params,
        ]);
    }

    public function settings() {
        $params = [
            'title' => getPageTitle([
                __('Settings'),
                __('Administration'),
            ]),
        ];

        return view('admin.settings', [
            'settings' => json_encode(Setting::where('name', 'site')->first()->pluck('data')[0], JSON_PRETTY_PRINT),
            'params' => $params,
        ]);
    }

    public function categories() {
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

    public function tags() {
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

    public function users() {
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

    public function writings() {
        $params = [
            'title' => getPageTitle([
                __('Writings'),
                __('Administration'),
            ]),
        ];

        return view('admin.writings', [
            'writings' => Writing::simplePaginate($this->pagination),
            'params' => $params,
        ]);
    }

    public function pages() {
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

    public function tools() {
        ob_start () ;
        phpinfo () ;
        $pinfo = ob_get_contents () ;
        ob_end_clean () ;

        $log = storage_path('logs/laravel.log');
        $params = [
            'title' => getPageTitle([
                __('Tools'),
                __('Administration'),
            ]),
            'log_file' => $log,
            'log_contents' => shell_exec('tail -n 100 ' . $log),
            'php_info' => $pinfo,
        ];

        return view('admin.tools', [
            'params' => $params,
        ]);
    }

    public function complaints() {
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
}
