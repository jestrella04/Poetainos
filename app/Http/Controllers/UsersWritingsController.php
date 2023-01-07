<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersWritingsController extends Controller
{
    public function index(User $user)
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';
        $head_msg = __('You are browsing the library of writings published by @:user.', ['user' => $user['username']]);

        if (auth()->check() && auth()->user()->is($user)) {
            $head_msg = __('You are browsing the library of writings published by yourself.');
        }

        $params = [
            'head_msg' => $head_msg,
            'title' => getPageTitle([
                __('Writings'),
                $user->getName()
                ]),
            'canonical' => $user->writingsPath(),
            'empty-head' => __('Where did the muses go?'),
            'empty-msg' => __('So sorry to tell you that :name has not yet published a writing.', ['name' => $user->firstName()]),
        ];

        if ('latest' === $sort) {
            $writings = $user->writings()
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = $user->writings()
            ->orderBy('views', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('likes' === $sort) {
            $writings = $user->writings()
            ->withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->simplePaginate($this->pagination);
        }

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params,
            'sort' => $sort,
        ]);
    }
}
