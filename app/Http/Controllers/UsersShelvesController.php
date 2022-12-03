<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersShelvesController extends Controller
{
    public function index(User $user)
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';
        $head_msg = __('You are browsing the library of writings bookmarked by @:user.', ['user' => $user['username']]);

        if (auth()->check() && auth()->user()->is($user)) {
            $head_msg = __('You are browsing the library of writings you have saved to your shelf.');
        }

        $params = [
            'head_msg' => $head_msg,
            'title' => getPageTitle([
                __('Shelf'),
                $user->getName()
                ]),
            'empty-head' => __('This shelf is empty'),
            'empty-msg' => __("We're afraid that :name has not added any writings to the shelf yet.", ['name' => $user->firstName()]),
            'empty-icon' => 'user-clock'
        ];

        if ('latest' === $sort) {
            $writings = $user->shelf()
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = $user->shelf()
            ->orderBy('views', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('likes' === $sort) {
            $writings = $user->shelf()
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->simplePaginate($this->pagination);
        }

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params,
            'sort' => $sort,
        ]);
    }
}
