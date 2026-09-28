<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Writing;
use App\Services\AgreementRecorder;
use App\Services\ContentDeleter;
use App\Services\EmailChanger;
use App\Services\ImageStorage;
use App\Services\SecurityLog;
use App\Services\ViewCounter;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

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
        request()->validate(['query' => 'required|string|min:2|max:50']);

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
            'isAuthorBlocked' => in_array($user->id, $this->blockedAuthorIds(), true),
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
     * Update the specified resource in storage, then open the user's account,
     * or the edited profile when someone else (an admin) made the change.
     */
    public function update(Request $request, User $user, ImageStorage $images, EmailChanger $emailChanger, SecurityLog $securityLog, AgreementRecorder $agreements): RedirectResponse
    {
        $this->authorize('update', $user);

        $isOwnProfile = $request->user()?->is($user) === true;

        // Only users editing their own profile are asked for their agreements
        $agreeingUser = $isOwnProfile ? $user : null;

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
            'avatar' => $this->imageUploadRule(),
            'avatar_remove' => 'nullable|boolean',
            ...$agreements->rules($agreeingUser),
        ]);

        $profile = $user->editableProfile();
        $currentAvatar = $profile->avatar;
        $isAvatarRemoved = isTruthy($request->input('avatar_remove'));
        $uploadedAvatar = $isAvatarRemoved ? null : $this->storeUploadedAvatar($request, $images);
        $avatar = $isAvatarRemoved ? null : ($uploadedAvatar ?? $currentAvatar);

        // Only an admin may change a user's role
        $fromRoleId = $user->role_id;
        $isRoleChanged = $request->input('role') !== null && $request->user()?->isAllowed('admin') === true && (int) $request->input('role') !== (int) $user->role_id;

        $newEmail = (string) $request->input('email');
        $isEmailChanged = $user->email !== $newEmail;

        // A user's own new address waits until they prove it receives mail, so a
        // hijacked session can't move the account away. An admin's change is
        // trusted and applied at once, unverified until the user proves it.
        $isEmailChangePending = $isEmailChanged === true && $isOwnProfile === true;
        $isEmailReplaced = $isEmailChanged === true && $isOwnProfile === false;

        try {
            DB::transaction(function () use ($request, $user, $profile, $avatar, $isRoleChanged, $isEmailReplaced, $newEmail): void {
                $profile->fill([
                    ...$request->only(['bio', 'website', 'location', 'interests', 'occupation', ...array_keys(UserProfile::SOCIAL_NETWORKS)]),
                    'avatar' => $avatar,
                ])->save();

                if ($isRoleChanged === true) {
                    $user->role_id = $request->input('role');
                }

                $user->name = $request->input('name');

                if ($isEmailReplaced === true) {
                    $user->email = $newEmail;
                    $user->email_verified_at = null;
                }

                $user->save();
            });
        } catch (Throwable $exception) {
            // Nothing refers to the new upload once the rows are rolled back
            $images->delete($uploadedAvatar);

            throw $exception;
        }

        // The replaced or removed avatar goes only once no row refers to it
        if ($avatar !== $currentAvatar) {
            $images->delete($currentAvatar);
        }

        if ($isRoleChanged === true) {
            $securityLog->record('Role changed', $request->user(), [
                'user_id' => $user->id,
                'from_role_id' => $fromRoleId,
                'to_role_id' => $request->input('role'),
            ]);
        }

        if ($isEmailChangePending === true) {
            $emailChanger->request($user, $newEmail);
        }

        if ($isEmailReplaced === true) {
            $user->sendEmailVerificationNotification();
        }

        $agreements->remember($request, $agreeingUser);

        Inertia::flash([
            'message' => $isEmailChangePending === true ? 'accounts.email-change-pending' : 'accounts.profile-updated',
            'color' => 'success',
        ]);

        return redirect($isOwnProfile ? route('users.account') : $user->path());
    }

    /**
     * Remove the specified resource from storage. Users deleting their own
     * account are logged out and go home; an admin deleting someone else goes
     * back to the admin table when deleting from there, else home.
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

        return $isOwnAccount === false && $request->routeIs('admin.*') ? back() : to_route('home');
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
                'pending_email',
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
     * Store the uploaded avatar, returning its path, or null when none was uploaded.
     */
    private function storeUploadedAvatar(Request $request, ImageStorage $images): ?string
    {
        $upload = $request->file('avatar');

        if ($upload instanceof UploadedFile && $upload->isValid()) {
            return $images->storeUpload($upload, 'avatars', self::AVATAR_SIZE, self::AVATAR_SIZE);
        }

        return null;
    }
}
