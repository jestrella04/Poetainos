<?php

namespace App\Models;

use App\Notifications\VerifyEmailCode;
use App\Services\VerificationCodes;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use NotificationChannels\WebPush\HasPushSubscriptions;

/**
 * @property-read UserProfile $profile Never null: an empty profile stands in until the user fills one in.
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable;

    /**
     * A valid username: word characters and single dots, not starting or ending with a dot.
     */
    public const USERNAME_PATTERN = '/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$/';

    /**
     * An @mention of a username. It may capture a sentence's closing dot,
     * which no username ends with, so callers trim trailing dots.
     */
    public const MENTION_PATTERN = '/\B@(\w[\w.]{0,44})/';

    /**
     * 8+ characters with an upper and a lower case letter, plus a digit or a
     * symbol. Mirrored by PASSWORD_PATTERN in resources/js/composables/validationRules.ts.
     */
    public const PASSWORD_PATTERN = '/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/';

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
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // Superseded by the profile and account columns; kept only until its data is verified and dropped
        'extra_info',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
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
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'wants_email_notifications' => 'boolean',
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

    /**
     * The URL of the avatar that withProfileFields('avatar') selects; null
     * when there is none or it wasn't selected.
     *
     * @return Attribute<?string, never>
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => array_key_exists('avatar', $this->attributes) ? storageUrl($this->attributes['avatar']) : null,
        );
    }

    public function getName(): string
    {
        $name = $this->name ?? '';

        return $name !== '' ? $name : $this->username;
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
        $columns = ['id', 'username', 'name'];

        if ($withKarma === true) {
            $columns[] = 'karma';
        }

        return $query->select($columns)->withProfileFields('avatar');
    }

    /**
     * Add profile fields to the selected columns under their own names,
     * keeping the flat shape listings send (`avatar`, `bio`…) without
     * loading the profile relation.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeWithProfileFields(Builder $query, string ...$fields): Builder
    {
        foreach ($fields as $field) {
            $query->addSelect([
                $field => UserProfile::select($field)->whereColumn('user_profiles.user_id', 'users.id'),
            ]);
        }

        return $query;
    }

    /**
     * @return HasOne<UserProfile, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class)->withDefault();
    }

    /**
     * The profile, created on first use so it can be saved.
     */
    public function editableProfile(): UserProfile
    {
        return $this->profile()->firstOrCreate([]);
    }

    /**
     * @return HasMany<SocialAccount, $this>
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
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
     * The likes the user gave to writings and comments.
     *
     * @return HasMany<Like, $this>
     */
    public function givenLikes(): HasMany
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
        return $this->givenLikes()->where('likeable_type', Writing::class)->select('likeable_id');
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

    public function isAllowed(string $task): bool
    {
        return $this->role?->grants($task) === true;
    }

    public function isInAgreement(): bool
    {
        return $this->terms_accepted_at !== null && $this->privacy_accepted_at !== null;
    }

    public function acceptAgreements(): void
    {
        $this->terms_accepted_at ??= Carbon::now();
        $this->privacy_accepted_at ??= Carbon::now();
        $this->save();
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
        return $this->wants_email_notifications !== false;
    }

    public function setEmailNotifications(bool $isEnabled): void
    {
        $this->wants_email_notifications = $isEnabled;
        $this->save();
    }

    /**
     * Email a fresh one-time code, replacing any previous one. The code is
     * bound to the current address so changing email invalidates it.
     */
    public function sendEmailVerificationNotification(): void
    {
        $code = app(VerificationCodes::class)->issue($this, VerificationCodes::PURPOSE_EMAIL_VERIFICATION);

        $this->notify(new VerifyEmailCode($code, VerificationCodes::CODE_MINUTES));
    }

    /**
     * Mark the email as verified when the code matches.
     */
    public function verifyEmailWithCode(string $code): bool
    {
        if (app(VerificationCodes::class)->verify($this, VerificationCodes::PURPOSE_EMAIL_VERIFICATION, $code) === false) {
            return false;
        }

        return $this->markEmailAsVerified();
    }
}
