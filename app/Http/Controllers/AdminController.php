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
                'users' => ReadableHumanNumber(User::all()->count()),
                'writings' => ReadableHumanNumber(Writing::all()->count()),
                'comments' => ReadableHumanNumber(Comment::all()->count() + Reply::all()->count()),
                'categories' => ReadableHumanNumber(Category::all()->count()),
                'tags' => ReadableHumanNumber(Tag::all()->count()),
                'votes' => ReadableHumanNumber(Vote::all()->count()),
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
