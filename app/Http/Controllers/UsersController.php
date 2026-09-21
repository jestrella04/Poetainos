<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChain;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Paginator<int, User>|Response
     */
    public function index(): Paginator|Response
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
            'extra_info->location AS location',
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
        } else {
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
            'totalAuthors' => User::has('writings')->count(),
            'users' => Inertia::optional(fn () => $users),
        ]);
    }

    /**
     * Query list of matching resources.
     *
     * @return Collection<int, User>
     */
    public function query(): Collection
    {
        $wildcard = '%'.request('query').'%';

        return User::where('name', 'like', $wildcard)
            ->orWhere('username', 'like', $wildcard)
            ->select('name', 'username')
            ->take($this->pagination ?? 15)
            ->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
    {
        // Increment writing views
        $user->incrementViews();

        // Update Aura / karma
        $user->updateAura();
        // $user->updateKarma();

        $authUser = Auth::user();

        return Inertia::render('users/PoUsersShow', [
            'meta' => [
                'title' => getPageTitle([$user->getName(), __('Writers')]),
                'canonical' => $user->path(),
            ],
            // MariaDB's JSON_VALUE (what `extra_info->social` compiles to) returns NULL for objects,
            // so `social` is decoded from the bound model instead of selected.
            'user' => User::select(
                'id',
                'username',
                'name',
                'profile_views',
                'aura',
                'karma',
                'created_at',
                'extra_info->bio AS bio',
                'extra_info->avatar AS avatar',
                'extra_info->website AS website',
                'extra_info->location AS location',
                'extra_info->interests AS interests',
                'extra_info->occupation AS occupation',
            )
                ->where('id', $user->id)
                ->withCount(['writings', 'awards', 'likes', 'comments', 'shelf'])
                ->firstOrFail()
                ->setAttribute('social', json_encode($user->extra_info['social'] ?? [])),
            'authorWritings' => Inertia::optional(fn () => $user->writings()
                ->visibleTo($this->getBlockedUsers())
                ->withListingRelations()
                ->latest()
                ->simplePaginate($this->pagination)
                ->withPath(route('users.writings.index', $user))),
            'writings' => [
                'from_shelf' => randomWritingsWithAuthor($user->shelf()),
                'from_liked' => randomWritingsWithAuthor(
                    Writing::whereIn(
                        'id',
                        $user->likes()->where('likeable_type', Writing::class)->pluck('likeable_id'),
                    )
                ),
            ],
            'isAuthorBlocked' => $authUser !== null ? $authUser->isAuthorBlocked($user) : false,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
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
     * @return array<string, string>
     */
    public function update(Request $request, User $user): array
    {
        $this->authorize('update', $user);

        // Validate user input
        request()->validate([
            'role' => 'nullable|integer|exists:roles,id',
            'name' => 'required|string|min:3|max:250',
            'email' => 'required|email|min:3|max:250',
            'bio' => 'nullable|string|min:3|max:300',
            'location' => 'nullable|string|min:3|max:250',
            'occupation' => 'nullable|string|min:3|max:100',
            'interests' => 'nullable|string|min:3|max:250',
            'website' => 'nullable|url|max:250',
            'twitter' => 'nullable|string|min:3|max:250',
            'threads' => 'nullable|string|min:3|max:250',
            'instagram' => 'nullable|string|min:3|max:100',
            'facebook' => 'nullable|string|min:3|max:250',
            'youtube' => 'nullable|string|min:3|max:100',
            'goodreads' => 'nullable|string|min:3|max:250',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:'.getSiteConfig('uploads_max_file_size'),
            'avatar-remove' => 'nullable|boolean',
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ]);

        // Working with avatars
        $remove = (bool) request('avatar-remove');

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
        if (! empty(request('role')) && Auth::user()?->isAllowed('admin') === true) {
            $user->role_id = request('role');
        }

        // A changed email is unverified until the user proves they own it again
        $emailChanged = $user->email !== request('email');

        // Persist to database
        $user->name = request('name');
        $user->email = request('email');
        $user->extra_info = $extraInfo;

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

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
     * @return array<int, mixed>|RedirectResponse|Redirector
     */
    public function destroy(User $user): array|RedirectResponse|Redirector
    {
        $this->authorize('delete', $user);
        $user->deleteOrFail();

        // Delete related notifications
        $user->notifications()->delete();
        DB::table('notifications')->where('data->user_id', $user->id)->delete();

        // Delete related likes
        $user->likes()->delete();

        if (Auth::user()?->id === $user->id) {
            request()
                ->session()
                ->flash('flash', __('Your account and related data have been deleted successfully!'));

            return redirect(route('home'));
        }

        return [];
    }

    /**
     * Get the currently authenticated user.
     */
    public function me(Request $request): User
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        return $user;
    }

    /**
     * Recalculate the given user's karma.
     */
    public function karma(User $user): \Illuminate\Http\Response
    {
        $this->authorize('update', $user);

        $user->updateKarma();

        return response($user->karma);
    }

    /**
     * Block another user.
     *
     * @return array<int, mixed>
     */
    public function blockUser(User $user): array
    {
        Auth::user()?->block($user);

        return [];
    }

    /**
     * Display the specified resource.
     */
    public function account(): Response
    {
        $user = Auth::user();
        $this->authorize('delete', $user);

        if ($user === null) {
            abort(401);
        }

        $user->loadCount(['writings', 'shelf', 'likes', 'blockedAuthors']);

        return Inertia::render('users/PoUsersAccount', [
            'meta' => [
                'title' => getPageTitle([__('My account')]),
            ],
            'account' => $user->only([
                'created_at',
                'writings_count',
                'shelf_count',
                'likes_count',
                'blocked_authors_count',
            ]),
            'notifications' => [
                'email' => isset($user->extra_info['notifications']['email'])
                  ? isTruthy($user->extra_info['notifications']['email'])
                  : true,
            ],
        ]);
    }
}
