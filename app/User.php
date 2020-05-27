<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password',
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
        return route('users_writings.show', $this->username);
    }

    public function shelfPath()
    {
        return route('users_shelves.show', $this->username);
    }

    public function avatarPath()
    {
        $path = '/static/uploads/avatars/' . $this->avatar_path;

        if (is_file(public_path($path))) {
            return $path;
        }
    }

    public function fullName()
    {
        if (! empty($this->name) && ! empty($this->last_name)) {
            return $this->name . ' ' . $this->last_name;
        }

        return $this->username;
    }

    public function initials()
    {
        if (! empty($this->name) && ! empty($this->last_name)) {
            return strtoupper(substr($this->name, 0, 1) . substr($this->last_name, 0, 1));
        }

        return strtoupper(substr($this->username, 0, 1));
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

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function role()
    {
        return $this->hasOne(Role::class);
    }

    public function incrementViews()
    {
        DB::table($this->getTable())->whereId($this->id)->increment('profile_views');
    }

    public function updateAura()
    {
        // Count user content
        $writings = $this->writings->count();
        $votes = $this->votes->count();
        $comments = $this->comments->count();
        $shelf = $this->shelf->count();
        $views = $this->profile_views;
        $hood = $this->hood->count();
        $extendedHood = $this->fellowHood($sount = true);

        // Get points from settings
        $pointsWritings = getSiteConfig('aura.points.user.writing');
        $pointsVotes = getSiteConfig('aura.points.user.vote');
        $pointsComments = getSiteConfig('aura.points.user.comment');
        $pointsShelf = getSiteConfig('aura.points.user.shelf');
        $pointsViews = getSiteConfig('aura.points.user.views');
        $pointsHood = getSiteConfig('aura.points.user.hood');
        $pointsExtendedHood = getSiteConfig('aura.points.user.extended_hood');
        $basePoints = $pointsWritings + $pointsVotes + $pointsComments + $pointsShelf + $pointsViews + $pointsHood + $pointsExtendedHood;

        // Calculate points as per settings
        $pointsWritings = $pointsWritings * $writings;
        $pointsVotes = $pointsVotes * $votes;
        $pointsComments = $pointsComments * $comments;
        $pointsShelf = $pointsShelf * $shelf;
        $pointsViews = $pointsViews * $views;
        $pointsHood = $pointsHood * $hood;
        $pointsExtendedHood = $pointsExtendedHood * $extendedHood;
        $totalPoints = $pointsWritings + $pointsVotes + $pointsComments + $pointsShelf + $pointsViews + $pointsHood + $pointsExtendedHood;

        // Do the math
        $aura = ($totalPoints / $basePoints) * (1 + ($basePoints / 100));
        $aura = number_format($aura, 2);

        // Persist to the database
        DB::table($this->getTable())->whereId($this->id)->update(['aura' => $aura, 'aura_updated_at' => Carbon::now()]);
    }
}
