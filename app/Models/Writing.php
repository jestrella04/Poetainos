<?php

namespace App\Models;

use App\Notifications\WritingFeatured;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Writing extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'type_id',
        'title',
        'slug',
        'text',
        'extra_info',
        'aura',
        'aura_updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'extra_info' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function path()
    {
        return route('writings.show', $this->slug);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function excerpt()
    {
        $len = mb_strlen($this->text);

        if ($len < 400) {
            return $this->text;
        }

        return mb_substr($this->text, 0, 400) . '...';
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function likers()
    {
        $likes = $this->likes()->pluck('user_id');
        return User::select('id', 'username', 'name', 'extra_info->avatar AS avatar')->whereIn('id', $likes)->get();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function categoriesAsString($delimiter = ', ')
    {
        $array = $this->categories
            ->map(function ($category) {
                return $category->name;
            })
            ->toArray();

        return implode($delimiter, $array);
    }

    public function tagsAsString($delimiter = ', ')
    {
        $array = $this->tags
            ->map(function ($tag) {
                return $tag->name;
            })
            ->toArray();

        return implode($delimiter, $array);
    }

    public function shelf()
    {
        return $this->belongsToMany(User::class, 'shelves');
    }

    public function incrementViews()
    {
        DB::table($this->getTable())->whereId($this->id)->increment('views');
    }

    public function updateAura()
    {
        // What's the minimum to be featured at home?
        $auraHome = getSiteConfig('aura.min_at_home');

        // Count user content
        $likes = $this->likes()->count();
        $comments = $this->comments()->count();
        $shelf = $this->shelf()->count();
        $views = $this->views;

        // Get points from settings
        $pointsLikes = getSiteConfig('aura.points.writing.upvote');
        $pointsComments = getSiteConfig('aura.points.writing.comment');
        $pointsShelf = getSiteConfig('aura.points.writing.shelf');
        $pointsViews = getSiteConfig('aura.points.writing.views');
        $basePoints = $pointsLikes + $pointsComments + $pointsShelf + $pointsViews;

        // Calculate points as per settings
        $pointsLikes = $pointsLikes * $likes;
        $pointsComments = $pointsComments * $comments;
        $pointsShelf = $pointsShelf * $shelf;
        $pointsViews = $pointsViews * $views;
        $totalPoints = $pointsLikes + $pointsComments + $pointsShelf + $pointsViews;

        // Do the math
        $auraNew = ($totalPoints / $basePoints) * (1 + ($basePoints / 100));
        $auraNew = number_format($auraNew, 2);

        // Check when writing was posted (in days)
        $postedAt = Carbon::parse($this->created_at)->diffInDays();

        // Check if writing is awarded
        $awarded = isset($this->home_posted_at);

        // Persist to the database
        if ($auraNew >= $auraHome && $postedAt <= 31 && !$awarded) {
            DB::table($this->getTable())->whereId($this->id)->update([
                'aura' => $auraNew,
                'aura_updated_at' => Carbon::now(),
                'home_posted_at' => Carbon::now(),
            ]);

            $this->author->notify(new WritingFeatured($this));
        } else {
            DB::table($this->getTable())->whereId($this->id)->update([
                'aura' => $auraNew,
                'aura_updated_at' => Carbon::now()
            ]);
        }
    }

    public function externalLink()
    {
        if (!empty($this->extra_info['link'])) {
            return $this->extra_info['link'];
        }
    }

    public function coverPath()
    {
        if (!empty($this->extra_info['cover'])) {
            $path = '/storage/' . $this->extra_info['cover'];

            if (is_file(public_path($path))) {
                return $path;
            }
        }
    }



    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'complainable');
    }
}
