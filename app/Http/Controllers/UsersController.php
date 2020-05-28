<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sort = request('sort') ?? 'featured';
        $params = [
            'title' => __('Writers'),
        ];

        if ('latest' === $sort) {
            $users = User::latest()
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $users = User::orderBy('profile_views', 'desc')
            ->simplePaginate($this->pagination);
        } else {
            $users = User::orderBy('aura', 'desc')
            ->simplePaginate($this->pagination);
        }

        return view('users.index', [
            'users' => $users,
            'params' => $params
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $params = [
            'single_entry' => true,
            'title' => $user->fullName(),
        ];

        // Increment writing views
        $user->incrementViews();

        // Update Aura
        $user->updateAura();

        return view('users.show', [
            'user' => $user,
            'params' => $params
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $params = [
            'title' => 'Modify profile',
            'roles' => ['user', 'moderator', 'admin'],
        ];

        return view('users.edit', [
            'params' => $params,
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        return $user->delete();
    }
}
