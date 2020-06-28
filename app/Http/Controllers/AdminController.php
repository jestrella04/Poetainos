<?php

namespace App\Http\Controllers;

use App\Category;
use App\Comment;
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
        return view('admin.summary', [
            'counters' => [
                'users' => [
                    'title' => __('Users'),
                    'count' => ReadableHumanNumber(User::all()->count()),
                ],
                'writings' => [
                    'title' => __('Writings'),
                    'count' => ReadableHumanNumber(Writing::all()->count()),
                ],
                'comments' => [
                    'title' => __('Comments'),
                    'count' => ReadableHumanNumber(Comment::all()->count() + Reply::all()->count()),
                ],
                'categories' => [
                    'title' => __('Categories'),
                    'count' => ReadableHumanNumber(Category::all()->count()),
                ],
                'tags' => [
                    'title' => __('Tags'),
                    'count' => ReadableHumanNumber(Tag::all()->count()),
                ],
                'likes' => [
                    'title' => __('Likes'),
                    'count' => ReadableHumanNumber(Vote::all()->count()),
                ],
            ]
        ]);
    }

    public function settings() {
        return view('admin.settings', [
            'settings' => json_encode(Setting::where('name', 'site')->first()->pluck('data')[0], JSON_PRETTY_PRINT),
        ]);
    }

    public function categories() {
        return view('admin.categories', [
            'categories' => Category::simplePaginate($this->pagination),
        ]);
    }

    public function tags() {
        return view('admin.tags', [
            'tags' => Tag::simplePaginate($this->pagination),
        ]);
    }

    public function users() {
        return view('admin.users', [
            'users' => User::simplePaginate($this->pagination),
        ]);
    }

    public function writings() {
        return view('admin.writings', [
            'writings' => Writing::simplePaginate($this->pagination),
        ]);
    }

    public function pages() {
        return view('admin.pages', [
            'pages' => Page::simplePaginate($this->pagination),
        ]);
    }
}
