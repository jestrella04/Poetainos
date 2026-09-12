<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChain;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return User|Paginator|Response
     */
    public function index()
    {
        $sort = resolveSort(['latest', 'popular', 'featured'], 'featured');
        $users = User::select(
            'id',
            'username',
            'name',
            'profile_views',
            'aura',
            'karma',
            'extra_info->bio AS bio',
            // 'extra_info->social AS social',
            'extra_info->avatar AS avatar',
            // 'extra_info->website AS website',
            // 'extra_info->location AS location',
            // 'extra_info->interests AS interests',
        )
            ->has('writings')
            ->withCount(['writings', 'awards', 'likes', 'comments', 'shelf']);

        if ($sort === 'latest') {
            $users = $users->latest()->simplePaginate($this->pagination)->withQueryString();
        } elseif ($sort === 'popular') {
            $users = $users
                ->orderBy('profile_views', 'desc')
                ->simplePaginate($this->pagination)
                ->withQueryString();
        } elseif ($sort === 'featured') {
            $users = $users
                ->orderByRaw('(CASE WHEN `karma` IS NULL THEN \'F\' ELSE `karma` END) ASC')
                ->orderBy('aura', 'desc')
                ->simplePaginate($this->pagination)
                ->withQueryString();
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
            'users' => Inertia::optional(fn () => $users),
        ]);
    }

    /**
     * Query list of matching resources.
     *
     * @return Collection
     */
    public function query()
    {
        $wildcard = '%'.request('query').'%';

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
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): void
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(User $user)
    {
        // Increment writing views
        $user->incrementViews();

        // Update Aura / karma
        $user->updateAura();
        // $user->updateKarma();

        $authUser = auth()->user();

        return Inertia::render('users/PoUsersShow', [
            'meta' => [
                'title' => getPageTitle([$user->getName(), __('Writers')]),
                'canonical' => $user->path(),
            ],
            'user' => User::select(
                'id',
                'username',
                'name',
                'profile_views',
                'aura',
                'karma',
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
                'from_author' => $user
                    ->writings()
                    ->with([
                        'author' => function ($query): void {
                            $query->forAuthorSummary();
                        },
                    ])
                    ->inRandomOrder()
                    ->take(5)
                    ->get(),
                'from_shelf' => $user
                    ->shelf()
                    ->with([
                        'author' => function ($query): void {
                            $query->forAuthorSummary();
                        },
                    ])
                    ->inRandomOrder()
                    ->take(5)
                    ->get(),
                'from_liked' => Writing::whereIn(
                    'id',
                    $user->likes()->where('likeable_type', Writing::class)->pluck('likeable_id'),
                )
                    ->with([
                        'author' => function ($query): void {
                            $query->forAuthorSummary();
                        },
                    ])
                    ->inRandomOrder()
                    ->take(5)
                    ->get(),
            ],
            'isAuthorBlocked' => auth()->check() ? $authUser->isAuthorBlocked($user) : false,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
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
            'threads' => 'nullable|string|min:3|max:40',
            'instagram' => 'nullable|string|min:3|max:40',
            'facebook' => 'nullable|string|min:3|max:40',
            'youtube' => 'nullable|string|min:3|max:40',
            'goodreads' => 'nullable|string|min:3|max:40',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:'.getSiteConfig('uploads_max_file_size'),
            'avatar-remove' => 'nullable|boolean',
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ]);

        // Working with avatars
        $remove = request('avatar-remove') || false;

        if ($remove) {
            $avatar = '';
        } elseif ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Persist the image
            $avatar = $request->file('avatar')->store('avatars');
            $avatarRealPath = storage_path('app/'.$avatar);

            // Scale the image
            Image::read($avatarRealPath)->cover(512, 512)->save();

            // Optimize the image
            app(OptimizerChain::class)->optimize($avatarRealPath);
        }

        // Create the extra info array
        $extraInfo = [
            'bio' => request('bio') ?? '',
            'social' => [
                'twitter' => request('twitter') ?? '',
                'threads' => request('threads') ?? '',
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

        // Only an admin may change a user's role
        if (! empty(request('role')) && auth()->user()->isAllowed('admin')) {
            $user->role_id = request('role');
        }

        // Persist to database
        $user->name = request('name');
        $user->email = request('email');
        $user->extra_info = $extraInfo;
        $user->save();

        // Persist user agreements to avoid asking again
        if (request('service_agreement') && request('privacy_agreement')) {
            $user->acceptAgreements();
        }

        // Set response data
        return [
            'url' => $user->path(),
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array|RedirectResponse|Redirector
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->deleteOrFail();

        // Delete related notifications
        $user->notifications()->delete();
        DatabaseNotification::where('data->user_id', $user->id)->delete();

        // Delete related likes
        $user->likes()->delete();

        if (auth()->user()->id === $user->id) {
            request()
                ->session()
                ->flash('flash', __('Your account and related data have been deleted successfully!'));

            return redirect(route('home'));
        }

        return [];
    }

    /**
     * Get the currently authenticated user.
     *
     * @return User
     */
    public function me(Request $request)
    {
        return $request->user();
    }

    /**
     * Recalculate the given user's karma.
     *
     * @return \Illuminate\Http\Response
     */
    public function karma(User $user)
    {
        $user->updateKarma();

        return response($user->karma);
    }

    /**
     * Block another user.
     *
     * @return array
     */
    public function blockUser(User $user)
    {
        auth()->user()->block($user);

        return [];
    }

    /**
     * Display the specified resource.
     *
     * @param  User  $user
     * @return Response
     */
    public function account()
    {
        $user = auth()->user();
        $this->authorize('delete', $user);

        $params = [];

        return Inertia::render('users/PoUsersAccount', [
            'meta' => [
                'title' => getPageTitle([__('My account')]),
            ],
            'notifications' => [
                'email' => isset($user->extra_info['notifications']['email'])
                  ? isTruthy($user->extra_info['notifications']['email'])
                  : true,
            ],
        ]);
    }
}
