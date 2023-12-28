<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'extra_info' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'username';
    }

    public function path()
    {
        return route('users.show', $this->username);
    }

    public function writingsPath()
    {
        return route('users_writings.index', $this->username);
    }

    public function shelfPath()
    {
        return route('users_shelves.index', $this->username);
    }

    public function avatarPath()
    {
        if (!empty($this->extra_info['avatar'])) {
            $path = '/storage/' . $this->extra_info['avatar'];

            if (is_file(public_path($path))) {
                return $path;
            }
        }
    }

    public function getName()
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        return $this->username;
    }

    public function firstName()
    {
        if (!empty($this->name)) {
            return explode(' ', $this->name)[0];
        }

        return $this->username;
    }

    public function initials()
    {
        if (!empty($this->name) && !empty($this->last_name)) {
            return strtoupper(substr($this->name, 0, 1) . substr($this->last_name, 0, 1));
        }

        return strtoupper(substr($this->username, 0, 1));
    }

    public function getTwitterUsername()
    {
        if (!empty($this->extra_info['social']['twitter'])) {
            return '@' . $this->extra_info['social']['twitter'];
        }

        return $this->getName();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function writings()
    {
        return $this->hasMany(Writing::class);
    }

    public function shelf()
    {
        return $this->belongsToMany(Writing::class, 'shelves');
    }

    public function hood()
    {
        return $this->belongsToMany(User::class, 'hoods');
    }

    public function fellowHood($count = false)
    {
        if ($count) {
            $count = DB::select('SELECT count(`user_id`) AS user_count FROM `hoods` WHERE `fellow_user_id` = ?', [$this->id]);
            return $count[0]->user_count;
        }

        return DB::select('SELECT `user_id` FROM `hoods` WHERE `fellow_user_id` = ?', [$this->id]);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function incrementViews()
    {
        DB::table($this->getTable())->whereId($this->id)->increment('profile_views');
    }

    public function updateAura()
    {
        // Count user content
        $writings = $this->writings->count();
        $likes = $this->likes->count();
        $comments = $this->comments->count() + $this->replies->count();
        $shelf = $this->shelf->count();
        $views = $this->profile_views;
        $hood = $this->hood->count();
        $extendedHood = $this->fellowHood($sount = true);

        // Get points from settings
        $pointsWritings = getSiteConfig('aura.points.user.writing');
        $pointsLikes = getSiteConfig('aura.points.user.like');
        $pointsComments = getSiteConfig('aura.points.user.comment');
        $pointsShelf = getSiteConfig('aura.points.user.shelf');
        $pointsViews = getSiteConfig('aura.points.user.views');
        $pointsHood = getSiteConfig('aura.points.user.hood');
        $pointsExtendedHood = getSiteConfig('aura.points.user.extended_hood');
        $basePoints = $pointsWritings + $pointsLikes + $pointsComments + $pointsShelf + $pointsViews + $pointsHood + $pointsExtendedHood;

        // Calculate points as per settings
        $pointsWritings = $pointsWritings * $writings;
        $pointsLikes = $pointsLikes * $likes;
        $pointsComments = $pointsComments * $comments;
        $pointsShelf = $pointsShelf * $shelf;
        $pointsViews = $pointsViews * $views;
        $pointsHood = $pointsHood * $hood;
        $pointsExtendedHood = $pointsExtendedHood * $extendedHood;
        $totalPoints = $pointsWritings + $pointsLikes + $pointsComments + $pointsShelf + $pointsViews + $pointsHood + $pointsExtendedHood;

        // Do the math
        $aura = ($totalPoints / $basePoints) * (1 + ($basePoints / 100));
        $aura = number_format($aura, 2);

        // Persist to the database
        DB::table($this->getTable())->whereId($this->id)->update(['aura' => $aura, 'aura_updated_at' => Carbon::now()]);
    }

    public static function featured($count = 20)
    {
        return self::orderByDesc('aura')->take($count)->get(['id', 'username', 'name', 'extra_info->avatar AS avatar']);
    }

    public function isAllowed($task)
    {
        if (null !== ($this->role)) {
            $this->task = $task;

            $allowed = Arr::first($this->role->permissions(), function ($value, $key) {
                return $this->task === $value['name'];
            });

            if ($allowed['enabled']) {
                return true;
            }
        }

        return false;
    }

    public function isInAgreement()
    {
        $terms = $this->extra_info['agreement']['terms_of_use'] ?? false;
        $privacy = $this->extra_info['agreement']['privacy_policy'] ?? false;

        if (isTruthy($terms) && isTruthy($privacy)) {
            return true;
        }

        return false;
    }

    public function acceptAgreements()
    {
        $info = $this->extra_info;
        $info['agreement']['terms_of_use'] = 'on';
        $info['agreement']['privacy_policy'] = 'on';

        $this->update(['extra_info' => $info]);
    }

    public function block($userToBlock)
    {
        return BlockedUser::firstOrCreate([
            'user_id' => $this->id,
            'blocked_user_id' => $userToBlock->id,
        ]);
    }

    public function getBlockedAuthors($asArrayOfIds = false)
    {
        $collection = $this->hasMany(BlockedUser::class);

        if ($asArrayOfIds) {
            $collection = $collection->pluck('blocked_user_id')->toArray();
        }

        return $collection;
    }

    public function isAuthorBlocked(User $author)
    {
        $blocked = $this->getBlockedAuthors($asArrayOfIds = true);

        if (in_array($author->id, $blocked)) {
            return true;
        }

        return false;
    }

    public function emailNotifications($enable)
    {
        $info = $this->extra_info;

        if (isTruthy($enable)) {
            $info['notifications']['email'] = 'on';
        } else {
            $info['notifications']['email'] = 'off';
        }

        $this->update(['extra_info' => $info]);
    }
}
