<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Writing;
use App\Services\ContentDeleter;
use App\Services\ImageStorage;
use App\Services\ViewCounter;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $users = User::select('id', 'username', 'name', 'profile_views', 'aura', 'karma')
            ->withProfileFields('bio', 'avatar', 'location')
            ->has('writings')
            ->withCount(['writings', 'awards', 'givenLikes', 'comments', 'shelf']);

        $users = match ($sort) {
            'latest' => $users->latest(),
            'popular' => $users->orderByDesc('profile_views'),
            default => $users->ranked(),
        };

        return $this->paginatedPage(
            fn (): Paginator => $users->simplePaginate($this->perPage)->withQueryString(),
            'users/PoUsersIndex',
            [
                'meta' => [
                    'title' => getPageTitle([__('Writers')]),
                    'canonical' => route('users.index'),
                ],
                'sort' => $sort,
                'totalAuthors' => fn (): int => User::has('writings')->count(),
            ],
            'users',
        );
    }

    /**
     * Writers whose name or username matches the query.
     *
     * @return Collection<int, User>
     */
    public function suggest(): Collection
    {
        $wildcard = '%'.escapeLike((string) request('query')).'%';

        return User::where('name', 'like', $wildcard)
            ->orWhere('username', 'like', $wildcard)
            ->select('name', 'username')
            ->take($this->perPage)
            ->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, ViewCounter $viewCounter): Response
    {
        $viewCounter->count($user);

        $authUser = Auth::user();

        return Inertia::render('users/PoUsersShow', [
            'meta' => [
                'title' => getPageTitle([$user->getName(), __('Writers')]),
                'canonical' => $user->path(),
            ],
            'user' => User::select('id', 'username', 'name', 'profile_views', 'aura', 'karma', 'created_at')
                ->withProfileFields('bio', 'avatar', 'website', 'location', 'interests', 'occupation')
                ->where('id', $user->id)
                ->withCount(['writings', 'awards', 'givenLikes', 'comments', 'shelf'])
                ->firstOrFail()
                ->setAttribute('social', $user->profile->socialHandles()),
            'authorWritings' => Inertia::optional(fn () => $user->writings()
                ->visibleTo($this->blockedAuthorIds())
                ->withListingRelations()
                ->latest()
                ->simplePaginate($this->perPage)
                ->withPath(route('users.writings.index', $user))),
            'writings' => [
                'from_shelf' => randomWritingsWithAuthor($user->shelf()->visibleTo($this->blockedAuthorIds())),
                'from_liked' => randomWritingsWithAuthor(
                    Writing::visibleTo($this->blockedAuthorIds())->whereIn('id', $user->likedWritingIds())
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
            'user' => $user->load('profile'),
            'agreement' => $user->isInAgreement(),
            'roles' => Auth::user()?->isAllowed('admin') === true ? Role::select('id', 'name')->get() : [],
        ]);
    }

    /**
     * Update the specified resource in storage, then open the profile.
     */
    public function update(Request $request, User $user, ImageStorage $images): RedirectResponse
    {
        $this->authorize('update', $user);

        $request->validate([
            'role' => 'nullable|integer|exists:roles,id',
            'name' => 'required|string|min:3|max:250',
            'email' => ['required', 'email', 'min:3', 'max:250', Rule::unique('users')->ignore($user)],
            'bio' => 'nullable|string|min:3|max:300',
            'location' => 'nullable|string|min:3|max:250',
            'occupation' => 'nullable|string|min:3|max:100',
            'interests' => 'nullable|string|min:3|max:250',
            'website' => 'nullable|url|max:250',
            ...array_map(fn (int $maxLength): string => 'nullable|string|min:3|max:'.$maxLength, UserProfile::SOCIAL_NETWORKS),
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:'.getSiteConfig('uploads_max_file_size'),
            'avatar-remove' => 'nullable|boolean',
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ]);

        $profile = $user->editableProfile();
        $profile->fill([
            ...$request->only(['bio', 'website', 'location', 'interests', 'occupation', ...array_keys(UserProfile::SOCIAL_NETWORKS)]),
            'avatar' => $this->resolveAvatar($request, $profile, $images),
        ])->save();

        // Only an admin may change a user's role
        if ($request->input('role') !== null && $request->user()?->isAllowed('admin') === true) {
            $user->role_id = $request->input('role');
        }

        // A changed email is unverified until the user proves they own it again
        $emailChanged = $user->email !== $request->input('email');

        // Persist to database
        $user->name = $request->input('name');
        $user->email = $request->input('email');

        if ($emailChanged === true) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged === true) {
            $user->sendEmailVerificationNotification();
        }

        // Persist user agreements to avoid asking again
        if (isTruthy($request->input('service_agreement')) && isTruthy($request->input('privacy_agreement'))) {
            $user->acceptAgreements();
        }

        Inertia::flash(['message' => 'accounts.profile-updated', 'color' => 'success']);

        return redirect($user->path());
    }

    /**
     * Remove the specified resource from storage. Users deleting their own
     * account are logged out; either way the home page follows.
     */
    public function destroy(Request $request, User $user, ContentDeleter $deleter): RedirectResponse
    {
        $this->authorize('delete', $user);

        $isOwnAccount = $request->user()?->is($user) === true;

        $deleter->deleteUser($user);

        if ($isOwnAccount === true) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Inertia::flash([
            'message' => $isOwnAccount === true ? 'accounts.account-deleted' : 'users.user-deleted',
            'color' => 'success',
        ]);

        return to_route('home');
    }

    /**
     * Display the specified resource.
     */
    public function account(): Response
    {
        $user = $this->requireAuthUser();
        $this->authorize('update', $user);

        $user->loadCount(['writings', 'shelf', 'givenLikes', 'blockedAuthors']);

        return Inertia::render('users/PoUsersAccount', [
            'meta' => [
                'title' => getPageTitle([__('My account')]),
            ],
            'account' => $user->only([
                'created_at',
                'writings_count',
                'shelf_count',
                'given_likes_count',
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
    private function resolveAvatar(Request $request, UserProfile $profile, ImageStorage $images): ?string
    {
        if (isTruthy($request->input('avatar-remove'))) {
            $images->delete($profile->avatar);

            return null;
        }

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatar = $images->storeUpload($request->file('avatar'), 'avatars', self::AVATAR_SIZE, self::AVATAR_SIZE);
            $images->delete($profile->avatar);

            return $avatar;
        }

        return $profile->avatar;
    }
}
