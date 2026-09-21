<?php

namespace App\Models;

use App\Notifications\VerifyEmailCode;
use App\Services\AuraCalculator;
use App\Services\EmailVerificationCodes;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use NotificationChannels\WebPush\HasPushSubscriptions;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'password_updated_at',
        'extra_info',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'extra_info' => 'array',
        ];
    }

    public function getRouteKeyName()
    {
        return 'username';
    }

    public function path(): string
    {
        return route('users.show', $this->username);
    }

    public function writingsPath(): string
    {
        return route('users.writings.index', $this->username);
    }

    public function getName(): string
    {
        $name = $this->name ?? '';

        return $name !== '' ? $name : $this->username;
    }

    public function initials(): string
    {
        $nameParts = preg_split('/\s+/', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY);

        if ($nameParts === false || count($nameParts) === 0) {
            return mb_strtoupper(mb_substr($this->username, 0, 1));
        }

        $initials = mb_substr($nameParts[0], 0, 1);

        if (count($nameParts) > 1) {
            $initials .= mb_substr(end($nameParts), 0, 1);
        }

        return mb_strtoupper($initials);
    }

    /**
     * The user's X (Twitter) handle for a mention, or their display name when they have none.
     */
    public function twitterHandleOrName(): string
    {
        $handle = ltrim($this->extra_info['social']['twitter'] ?? '', '@');

        return $handle !== '' ? '@'.$handle : $this->getName();
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Minimal author summary columns reused across every listing/eager-load
     * that only needs to display "who wrote this" (id, username, name,
     * avatar), optionally including karma.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeForAuthorSummary(Builder $query, bool $withKarma = false): Builder
    {
        $columns = ['id', 'username', 'name', 'extra_info->avatar AS avatar'];

        if ($withKarma === true) {
            $columns[] = 'karma';
        }

        return $query->select($columns);
    }

    /**
     * Best authors first: karma A to F (users without karma count as F), then aura.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeRanked(Builder $query): Builder
    {
        return $query->orderByRaw("COALESCE(karma, 'F') ASC")->orderBy('aura', 'desc');
    }

    /**
     * @return HasMany<Writing, $this>
     */
    public function writings(): HasMany
    {
        return $this->hasMany(Writing::class);
    }

    /**
     * @return BelongsToMany<Writing, $this>
     */
    public function shelf(): BelongsToMany
    {
        return $this->belongsToMany(Writing::class, 'shelves');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Like, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * The ids of the writings the user liked, for use as a `whereIn` subquery.
     *
     * @return HasMany<Like, $this>
     */
    public function likedWritingIds(): HasMany
    {
        return $this->likes()->where('likeable_type', Writing::class)->select('likeable_id');
    }

    /**
     * @return HasMany<Writing, $this>
     */
    public function awards(): HasMany
    {
        return $this->hasMany(Writing::class)->whereNotNull('home_posted_at');
    }

    public function incrementViews(): void
    {
        DB::table($this->getTable())->where('id', $this->id)->increment('profile_views');

        $this->profile_views++;
        $this->syncOriginalAttribute('profile_views');
    }

    public function updateAura(): void
    {
        app(AuraCalculator::class)->updateUserAura($this);
    }

    public function updateKarma(): self
    {
        app(AuraCalculator::class)->updateUserKarma($this);

        return $this;
    }

    public function isAllowed(string $task): bool
    {
        if ($this->role === null) {
            return false;
        }

        $permission = collect($this->role->permissions())->firstWhere('name', $task);

        return (bool) ($permission['enabled'] ?? false);
    }

    public function isInAgreement(): bool
    {
        $terms = $this->extra_info['agreement']['terms_of_use'] ?? false;
        $privacy = $this->extra_info['agreement']['privacy_policy'] ?? false;

        return isTruthy($terms) && isTruthy($privacy);
    }

    public function acceptAgreements(): void
    {
        $info = $this->extra_info;
        $info['agreement']['terms_of_use'] = 'on';
        $info['agreement']['privacy_policy'] = 'on';

        $this->update(['extra_info' => $info]);
    }

    public function block(User $userToBlock): BlockedUser
    {
        return BlockedUser::firstOrCreate([
            'user_id' => $this->id,
            'blocked_user_id' => $userToBlock->id,
        ]);
    }

    /**
     * @return HasMany<BlockedUser, $this>
     */
    public function blockedAuthors(): HasMany
    {
        return $this->hasMany(BlockedUser::class);
    }

    public function unblock(User $userToUnblock): void
    {
        $this->blockedAuthors()->where('blocked_user_id', $userToUnblock->id)->delete();
    }

    public function isAuthorBlocked(User $author): bool
    {
        return $this->blockedAuthors()->where('blocked_user_id', $author->id)->exists();
    }

    /**
     * Whether the user wants notification emails. Users who never chose get them.
     */
    public function wantsEmailNotifications(): bool
    {
        $setting = $this->extra_info['notifications']['email'] ?? null;

        return $setting === null || $setting === '' || isTruthy($setting);
    }

    public function setEmailNotifications(bool $enabled): void
    {
        $info = $this->extra_info;
        $info['notifications']['email'] = $enabled ? 'on' : 'off';

        $this->update(['extra_info' => $info]);
    }

    /**
     * Email a fresh one-time code, replacing any previous one. The code is
     * bound to the current address so changing email invalidates it.
     */
    public function sendEmailVerificationNotification(): void
    {
        $code = app(EmailVerificationCodes::class)->issue($this);

        $this->notify(new VerifyEmailCode($code, EmailVerificationCodes::CODE_MINUTES));
    }

    /**
     * Mark the email as verified when the code matches.
     */
    public function verifyEmailWithCode(string $code): bool
    {
        if (app(EmailVerificationCodes::class)->verify($this, $code) === false) {
            return false;
        }

        return $this->markEmailAsVerified();
    }
}
