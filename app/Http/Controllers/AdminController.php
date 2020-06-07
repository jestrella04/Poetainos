<?php

namespace App\Http\Controllers;

use App\Category;
use App\Setting;
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
            'settings' => Setting::all(),
        ]);
    }

    public function types() {
        return view('admin.types', [
            'types' => Type::all(),
        ]);
    }

    public function categories() {
        return view('admin.categories', [
            'categories' => Category::all(),
        ]);
    }

    public function users() {
        return view('admin.users', [
            'users' => User::all(),
        ]);
    }
}
