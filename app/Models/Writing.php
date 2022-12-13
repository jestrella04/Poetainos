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
        // Get the old aura
        $auraOld = $this->aura;

        // What's the minimun to be featured at home?
        $auraHome = getSiteConfig('aura.min_at_home');

        // Count user content
        $uplikes = $this->likes->where('like', '>', 0)->count();
        $downlikes = $this->likes->where('like', 0)->count();
        $replies = Reply::whereIn('comment_id', Comment::where('writing_id', $this->id)->pluck('id')->toArray())->count();
        $comments = $this->comments->count() + $replies;
        $shelf = $this->shelf->count();
        $views = $this->views;

        // Get points from settings
        $pointsUplikes = getSiteConfig('aura.points.writing.uplike');
        $pointsDownlikes = getSiteConfig('aura.points.writing.downlike');
        $pointsComments = getSiteConfig('aura.points.writing.comment');
        $pointsShelf = getSiteConfig('aura.points.writing.shelf');
        $pointsViews = getSiteConfig('aura.points.writing.views');
        $basePoints = $pointsUplikes + $pointsDownlikes + $pointsComments + $pointsShelf + $pointsViews;

        // Calculate points as per settings
        $pointsUplikes = $pointsUplikes * $uplikes;
        $pointsDownlikes = $pointsDownlikes * $downlikes;
        $pointsComments = $pointsComments * $comments;
        $pointsShelf = $pointsShelf * $shelf;
        $pointsViews = $pointsViews * $views;
        $totalPoints = $pointsUplikes + $pointsDownlikes + $pointsComments + $pointsShelf + $pointsViews;

        // Do the math
        $auraNew = ($totalPoints / $basePoints) * (1 + ($basePoints / 100));
        $auraNew = number_format($auraNew, 2);

        // Check when writing was posted (in days)
        $postedAt = Carbon::parse($this->created_at)->diffInDays();

        // Persist to the database
        if ($auraOld < $auraHome && $auraNew >= $auraHome && $postedAt <= 31) {
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
        if (! empty($this->extra_info['link'])) {
            return $this->extra_info['link'];
        }
    }

    public function coverPath()
    {
        if (! empty($this->extra_info['cover'])) {
            $path = '/storage/' . $this->extra_info['cover'];

            if (is_file(public_path($path))) {
                return $path;
            }
        }
    }

    public function likers()
    {
        $likers = $this->likes()->pluck('user_id');
        return User::whereIn('id', $likers);
    }

    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'complainable');
    }
}
