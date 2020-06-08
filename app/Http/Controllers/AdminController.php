<?php

namespace App\Http\Controllers;

use App\Category;
use App\Page;
use App\Setting;
use App\Tag;
use App\Type;
use App\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index() {
        return view('admin.index');
    }

    public function settings() {
        return view('admin.settings', [
            'settings' => json_encode(Setting::where('name', 'site')->first()->pluck('data')[0], JSON_PRETTY_PRINT),
        ]);
    }

    public function types() {
        return view('admin.types', [
            'types' => Type::simplePaginate($this->pagination),
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

    public function pages() {
        return view('admin.pages', [
            'pages' => Page::simplePaginate($this->pagination),
        ]);
    }
}
