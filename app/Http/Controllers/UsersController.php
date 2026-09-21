<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Writing;
use App\Services\ImageStorage;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    private const AVATAR_SIZE = 512;

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
            'extra_info->avatar AS avatar',
            'extra_info->location AS location',
        )
            ->has('writings')
            ->withCount(['writings', 'awards', 'likes', 'comments', 'shelf']);

        $users = match ($sort) {
            'latest' => $users->latest(),
            'popular' => $users->orderByDesc('profile_views'),
            default => $users->ranked(),
        };
        $users = $users->simplePaginate($this->pagination)->withQueryString();

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
        $wildcard = '%'.escapeLike((string) request('query')).'%';

        return User::where('name', 'like', $wildcard)
            ->orWhere('username', 'like', $wildcard)
            ->select('name', 'username')
            ->take($this->pagination)
            ->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
    {
        $this->countViewOnce($user);

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
                'from_shelf' => randomWritingsWithAuthor($user->shelf()->visibleTo($this->getBlockedUsers())),
                'from_liked' => randomWritingsWithAuthor(
                    Writing::visibleTo($this->getBlockedUsers())->whereIn(
                        'id',
                        $user->likes()->where('likeable_type', Writing::class)->select('likeable_id'),
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
            'roles' => Auth::user()?->isAllowed('admin') === true ? Role::select('id', 'name')->get() : [],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array<string, string>
     */
    public function update(Request $request, User $user, ImageStorage $images): array
    {
        $this->authorize('update', $user);

        // Validate user input
        $request->validate([
            'role' => 'nullable|integer|exists:roles,id',
            'name' => 'required|string|min:3|max:250',
            'email' => ['required', 'email', 'min:3', 'max:250', Rule::unique('users')->ignore($user)],
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

        // Keep whatever else is stored on the profile (notification settings, linked providers…)
        $user->extra_info = [
            ...($user->extra_info ?? []),
            ...$this->profileInfo($this->resolveAvatar($request, $user, $images)),
        ];

        // Only an admin may change a user's role
        if (request('role') !== null && $request->user()?->isAllowed('admin') === true) {
            $user->role_id = request('role');
        }

        // A changed email is unverified until the user proves they own it again
        $emailChanged = $user->email !== request('email');

        // Persist to database
        $user->name = request('name');
        $user->email = request('email');

        if ($emailChanged === true) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged === true) {
            $user->sendEmailVerificationNotification();
        }

        // Persist user agreements to avoid asking again
        if (isTruthy(request('service_agreement')) && isTruthy(request('privacy_agreement'))) {
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
     * @return array<int, mixed>|RedirectResponse
     */
    public function destroy(Request $request, User $user, ImageStorage $images): array|RedirectResponse
    {
        $this->authorize('delete', $user);

        $avatar = $user->extra_info['avatar'] ?? null;

        DB::transaction(function () use ($user): void {
            $user->deleteOrFail();

            // Delete related notifications
            $user->notifications()->delete();
            DB::table('notifications')->where('data->user_id', $user->id)->delete();

            // Delete related likes
            $user->likes()->delete();
        });

        $images->delete($avatar);

        if ($request->user()?->is($user) === true) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flash('message', 'accounts.account-deleted');

            return to_route('home');
        }

        return [];
    }

    /**
     * Get the currently authenticated user.
     */
    public function me(): User
    {
        return $this->requireAuthUser();
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
        $authUser = $this->requireAuthUser();

        abort_if($authUser->is($user), 422, __('You cannot block yourself.'));

        $authUser->block($user);

        return [];
    }

    /**
     * Unblock a previously blocked user.
     *
     * @return array<int, mixed>
     */
    public function unblockUser(User $user): array
    {
        $this->requireAuthUser()->unblock($user);

        return [];
    }

    /**
     * Display the specified resource.
     */
    public function account(): Response
    {
        $user = $this->requireAuthUser();
        $this->authorize('update', $user);

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
                'email' => $user->wantsEmailNotifications(),
            ],
        ]);
    }

    /**
     * The avatar path to store: the current one, a freshly uploaded one, or none when the user removed it.
     */
    private function resolveAvatar(Request $request, User $user, ImageStorage $images): string
    {
        $currentAvatar = $user->extra_info['avatar'] ?? '';

        if (isTruthy(request('avatar-remove'))) {
            $images->delete($currentAvatar);

            return '';
        }

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatar = $images->storeUpload($request->file('avatar'), 'avatars', self::AVATAR_SIZE, self::AVATAR_SIZE);
            $images->delete($currentAvatar);

            return $avatar;
        }

        return $currentAvatar;
    }

    /**
     * The profile fields the edit form owns, as stored in extra_info.
     *
     * @return array<string, mixed>
     */
    private function profileInfo(string $avatar): array
    {
        return [
            'bio' => request('bio') ?? '',
            'social' => [
                'twitter' => request('twitter') ?? '',
                'threads' => request('threads') ?? '',
                'instagram' => request('instagram') ?? '',
                'facebook' => request('facebook') ?? '',
                'youtube' => request('youtube') ?? '',
                'goodreads' => request('goodreads') ?? '',
            ],
            'avatar' => $avatar,
            'website' => request('website') ?? '',
            'location' => request('location') ?? '',
            'interests' => request('interests') ?? '',
            'occupation' => request('occupation') ?? '',
        ];
    }
}
