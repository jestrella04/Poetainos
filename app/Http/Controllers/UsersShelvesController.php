<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UsersShelvesController extends Controller
{
    public function index(User $user)
    {
        $sort = in_array(request('sort'), ['latest', 'popular']) ? request('sort') : 'latest';

        $params = [
            'section' => 'shelf',
            'title' => __('Shelf') . ' - ' . $user->getName(),
            'author' => $user,
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
        }

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params,
            'sort' => $sort,
        ]);
    }
}
