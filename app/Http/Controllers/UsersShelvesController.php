<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UsersShelvesController extends Controller
{
    public function index(User $user)
    {
        $sort = request('sort') ?? 'latest';

        $params = [
            'section' => 'shelf',
            'title' => __('Shelf') . ' - ' . $user->getName(),
            'author' => $user,
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
            'params' => $params
        ]);
    }
}
