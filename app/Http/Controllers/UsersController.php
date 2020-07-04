<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

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
            'title' => $user->getName(),
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
            'title' => __('Update profile'),
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

        // Validate user input
        request()->validate([
            'role' => 'nullable|integer|exists:roles,id',
            'name' => 'required|string|min:3|max:60',
            'email' => 'required|email|min:3|max:40',
            'bio' => 'nullable|string|min:3|max:300',
            'location' => 'nullable|string|min:3|max:40',
            'occupation' => 'nullable|string|min:3|max:40',
            'interests' => 'nullable|string|min:3|max:100',
            'website' => 'nullable|url|max:250',
            'twitter' => 'nullable|string|min:3|max:40',
            'instagram' => 'nullable|string|min:3|max:40',
            'facebook' => 'nullable|string|min:3|max:40',
            'youtube' => 'nullable|string|min:3|max:40',
            'goodreads' => 'nullable|string|min:3|max:40',
            'avatar' => 'nullable|file|image|max:' . getSiteConfig('uploads_max_file_size'),
            'avatar-remove' => 'nullable|boolean',
        ]);

        // Working with avatars
        $remove = request('avatar-remove') || false;

        if ($remove) {
            $avatar = '';
        } else if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Persist the image
            $avatar = $request->file('avatar')->store('avatars');
            $avatarRealPath = storage_path('app/' . $avatar);

            // Scale the image
            Image::make($avatarRealPath)->resize(128, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save();

            // Optimize the image
            app(\Spatie\ImageOptimizer\OptimizerChain::class)->optimize($avatarRealPath);
        }

        // Create the extra info array
        $extraInfo = [
            'bio' => request('bio') ?? '',
            'social' => [
                'twitter' => request('twitter') ?? '',
                'instagram' => request('instagram') ?? '',
                'facebook' => request('facebook') ?? '',
                'youtube' => request('youtube') ?? '',
                'goodreads' => request('goodreads') ?? '',
            ],
            'avatar' => $avatar ?? (isset($user->extra_info['avatar']) ? $user->extra_info['avatar'] : ''),
            'website' => request('website') ?? '',
            'location' => request('location') ?? '',
            'interests' => request('interests') ?? '',
            'occupation' => request('occupation') ?? '',
        ];

        // Persist to database
        $user->role_id = request('role');
        $user->name = request('name');
        $user->email = request('email');
        $user->extra_info = $extraInfo;
        $user->save();

        // Set response data
        $response = $user->toArray();
        $response['message'] = __('Your profile was successfully updated');
        $response['url'] = $user->path();

        // Output the response
        return $response;
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
        $user->delete();

        if (auth()->user()->id === $user->id) {
            request()->session()->flash('flash', __('Your account and related data have been deleted successfully!'));
            return redirect(route('home'));
        }

        return [
            'message' => __('User deleted successfully')
        ];
    }
}
