<?php

namespace App\Models;

use Carbon\Carbon;
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
        'aura',
        'karma',
        'aura_updated_at',
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
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'extra_info' => 'array',
    ];

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

    public function shelfPath(): string
    {
        return route('users.shelf.index', $this->username);
    }

    public function avatarPath(): ?string
    {
        if (! empty($this->extra_info['avatar'])) {
            $path = '/storage/'.$this->extra_info['avatar'];

            if (is_file(public_path($path))) {
                return $path;
            }
        }

        return null;
    }

    public function getName(): string
    {
        if (! empty($this->name)) {
            return $this->name;
        }

        return $this->username;
    }

    public function firstName(): string
    {
        if (! empty($this->name)) {
            return explode(' ', $this->name)[0];
        }

        return $this->username;
    }

    public function initials(): string
    {
        if (! empty($this->name) && ! empty($this->last_name)) {
            return strtoupper(substr($this->name, 0, 1).substr($this->last_name, 0, 1));
        }

        return strtoupper(substr($this->username, 0, 1));
    }

    public function getTwitterUsername(): string
    {
        if (! empty($this->extra_info['social']['twitter'])) {
            return '@'.$this->extra_info['social']['twitter'];
        }

        return $this->getName();
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

        if ($withKarma) {
            $columns[] = 'karma';
        }

        return $query->select($columns);
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
     * @return HasMany<Writing, $this>
     */
    public function awards(): HasMany
    {
        return $this->hasMany(Writing::class)->whereNotNull('home_posted_at');
    }

    public function incrementViews(): void
    {
        DB::table($this->getTable())->where('id', $this->id)->increment('profile_views');
    }

    /**
     * @param  array<string, int|float>  $count
     * @return array{base: int|float, total: int|float, score: float}
     */
    private function calcPoints(array $count): array
    {
        $countables = [
            'writings' => $count['writings'] ?? 0,
            'likes' => $count['likes'] ?? 0,
            'comments' => $count['comments'] ?? 0,
            'shelf' => $count['shelf'] ?? 0,
            'views' => $count['views'] ?? 0,
            'awards' => $count['awards'] ?? 0,
        ];

        // Get points from settings
        $weights = [
            'writings' => getSiteConfig('aura.points.user.writing'),
            'likes' => getSiteConfig('aura.points.user.like'),
            'comments' => getSiteConfig('aura.points.user.comment'),
            'shelf' => getSiteConfig('aura.points.user.shelf'),
            'views' => getSiteConfig('aura.points.user.views'),
            'awards' => getSiteConfig('aura.points.user.award'),
        ];

        return calculateWeightedAuraScore($countables, $weights);
    }

    public function updateAura(): void
    {
        // Count user content
        $user = User::where('id', $this->id)->withCount(['writings', 'likes', 'comments', 'shelf', 'awards'])->firstOrFail();
        $count = [
            'writings' => $user->writings_count,
            'likes' => $user->likes_count,
            'comments' => $user->comments_count,
            'shelf' => $user->shelf_count,
            'awards' => $user->awards_count,
            'views' => $this->profile_views,
        ];

        $points = $this->calcPoints($count);

        // Do the math
        if ($points['total'] > 0 && $points['base'] > 0) {
            // Persist to the database
            DB::table('users')->where('id', $this->id)->update([
                'aura' => $points['score'],
                'aura_updated_at' => Carbon::now(),
            ]);
        }
    }

    public function updateKarma(): self
    {
        // Count user content
        $dateTrigger = Carbon::now()->subDays(90);
        $count = [
            'likes' => $this->likes()->whereDate('created_at', '>=', $dateTrigger)->count(),
            'comments' => $this->comments()->whereDate('created_at', '>=', $dateTrigger)->count(),
            'shelf' => Shelf::where('user_id', $this->id)->whereDate('created_at', '>=', $dateTrigger)->count(),
            'awards' => $this->writings()->whereDate('home_posted_at', '>=', $dateTrigger)->count(),
        ];

        $points = $this->calcPoints($count);

        // Do the math
        if (inRange($points['total'], 0, 1000)) {
            $karma = 'F';
        } elseif (inRange($points['total'], 1000, 2000)) {
            $karma = 'D';
        } elseif (inRange($points['total'], 2000, 3000)) {
            $karma = 'C';
        } elseif (inRange($points['total'], 3000, 4000)) {
            $karma = 'B';
        } else {
            $karma = 'A';
        }

        // Persist to the database
        $this->update([
            'karma' => $karma,
            'aura_updated_at' => Carbon::now(),
        ]);

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

        if (isTruthy($terms) && isTruthy($privacy)) {
            return true;
        }

        return false;
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

    public function isAuthorBlocked(User $author): bool
    {
        $blocked = $this->blockedAuthors()->pluck('blocked_user_id')->toArray();

        if (in_array($author->id, $blocked)) {
            return true;
        }

        return false;
    }

    public function emailNotifications(string $enable): void
    {
        $info = $this->extra_info;

        if (isTruthy($enable)) {
            $info['notifications']['email'] = 'on';
        } else {
            $info['notifications']['email'] = 'off';
        }

        $this->update(['extra_info' => $info]);
    }

    public function todayEmpathySummary(): int
    {
        $likes = $this->likes()
            ->where('likeable_type', 'App\Models\Writing')
            ->whereDate('created_at', now()->today())
            ->pluck('likeable_id')
            ->all();

        $comments = $this->comments()
            ->distinct('writing_id')
            ->whereDate('created_at', now()->today())
            ->pluck('writing_id')
            ->all();

        $shelves = Shelf::where('user_id', $this->id)
            ->distinct('writing_id')
            ->whereDate('created_at', now()->today())
            ->pluck('writing_id')
            ->all();

        return count(array_unique(array_merge($likes, $comments, $shelves)));
    }
}
