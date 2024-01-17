<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Role;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Intervention\Image\Facades\Image;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'featured']) ? request('sort') : 'featured';
        $users = User::select(
            'id',
            'username',
            'name',
            'profile_views',
            'aura',
            'extra_info->bio AS bio',
            //'extra_info->social AS social',
            'extra_info->avatar AS avatar',
            //'extra_info->website AS website',
            //'extra_info->location AS location',
            //'extra_info->interests AS interests',
        )->withCount(['writings', 'awards', 'likes', 'comments', 'shelf']);

        if ('latest' === $sort) {
            $users = $users->latest()->simplePaginate($this->pagination)->withQueryString();
        } elseif ('popular' === $sort) {
            $users = $users->orderBy('profile_views', 'desc')->simplePaginate($this->pagination)->withQueryString();
        } elseif ('featured' === $sort) {
            $users = $users->orderBy('aura', 'desc')->simplePaginate($this->pagination)->withQueryString();
        }

        if (request()->expectsJson()) {
            return $users;
        }

        return Inertia::render('users/PoUsersIndex', [
            'meta' => [
                'title' => getPageTitle([__('Writers')]),
                'canonical' => route('users.index'),
            ],
            'sort' => $sort,
            'users' => $users,
        ]);
    }

    /**
     * Query list of matching resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function query()
    {
        $wildcard = '%' . request('query') . '%';

        return User::where('name', 'like', $wildcard)
            ->orWhere('username', 'like', $wildcard)
            ->select('name', 'username')
            ->take($this->pagination)
            ->get();
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
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\View\View
     */
    public function show(User $user)
    {
        // Increment writing views
        $user->incrementViews();

        // Update Aura
        $user->updateAura();

        $authUser = auth()->check() ? User::find(auth()->user()->id) : null;

        return Inertia::render('users/PoUsersShow', [
            'meta' => [
                'title' => getPageTitle([
                    $user->getName(),
                    __('Writers'),
                ]),
                'canonical' => $user->path(),
            ],
            'user' => User::select(
                'id',
                'username',
                'name',
                'profile_views',
                'aura',
                'created_at',
                'extra_info->bio AS bio',
                'extra_info->social AS social',
                'extra_info->avatar AS avatar',
                'extra_info->website AS website',
                'extra_info->location AS location',
                'extra_info->interests AS interests',
            )
                ->whereId($user->id)
                ->withCount(['writings', 'awards', 'likes', 'comments', 'shelf'])
                ->firstOrFail(),
            'writings' => [
                'from_author' => $user->writings()
                    ->with(['author' => function ($query) {
                        $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
                    }])
                    ->inRandomOrder()->take(5)->get(),
                'from_shelf' => $user->shelf()
                    ->with(['author' => function ($query) {
                        $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
                    }])
                    ->inRandomOrder()->take(5)->get(),
                'from_liked' => Writing::whereIn('id', $user->likes()->where('likeable_type', Writing::class)->pluck('likeable_id'))
                    ->with(['author' => function ($query) {
                        $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
                    }])
                    ->inRandomOrder()->take(5)->get(),
            ],
            'isAuthorBlocked' => auth()->check() ? $authUser->isAuthorBlocked($user) : false
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return Inertia::render('users/PoUsersForm', [
            'meta' => [
                'title' => getPageTitle([__('Update profile')]),
            ],
            'user' => $user,
            'agreement' => $user->isInAgreement(),
            'roles' => Role::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return array
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
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
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
            })->crop(128, 128)->save();

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

        // Check if already accepted agreements
        if ($user->isInAgreement()) {
            $extraInfo['agreement']['terms_of_use'] = 'on';
            $extraInfo['agreement']['privacy_policy'] = 'on';
        }

        // Check if a user role is set
        if (!empty(request('role'))) {
            $user->role_id = request('role');
        }

        // Persist to database
        $user->name = request('name');
        $user->email = request('email');
        $user->extra_info = $extraInfo;
        $user->save();

        // Persist user agreements to avoid asking again
        if (!empty(request('service_agreement') && !empty(request('privacy_agreement')))) {
            $user->acceptAgreements();
        }

        // Set response data
        return [
            'url' => $user->path()
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        $user->notifications()->delete();

        if (auth()->user()->id === $user->id) {
            request()->session()->flash('flash', __('Your account and related data have been deleted successfully!'));
            return redirect(route('home'));
        }

        return [];
    }

    /**
     * Block another user.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    public function blockUser(User $user)
    {
        $blockingUser = User::find(auth()->user()->id);
        $blockingUser->block($user);

        return [];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\View\View
     */
    public function account(User $user)
    {
        $this->authorize('delete', $user);

        $params = [];

        return Inertia::render('users/PoUsersAccount', [
            'meta' => [
                'title' => getPageTitle([
                    $user->getName(),
                    __('Writers'),
                ]),
            ],
            'notifications' => [
                'email' => isset($user->extra_info['notifications']['email'])
                    ? isTruthy($user->extra_info['notifications']['email'])
                    : true,
            ]
        ]);
    }
}
