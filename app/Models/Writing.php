<?php

namespace App\Models;

use App\Notifications\WritingFeatured;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Writing extends Model
{
    use HasFactory;

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

    public function mainCategory()
    {
        return $this->belongsToMany(Category::class)->whereNull('parent_id');
    }

    public function altCategories()
    {
        return $this->belongsToMany(Category::class)->whereNotNull('parent_id');
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

        return mb_substr($this->text, 0, 400).'...';
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
        $writing = Writing::whereId($this->id)->withCount(['likes', 'comments', 'shelf'])->firstOrFail();
        $likes = $writing->likes_count;
        $comments = $writing->comments_count;
        $shelf = $writing->shelf_count;
        $views = $this->views;

        // Get points from settings
        $pointsLikes = getSiteConfig('aura.points.writing.like');
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
        if ($basePoints <= 0) {
            return;
        }

        // Reduces algebraically to totalPoints / (4 * basePoints); 4 is the
        // count of countables (likes, comments, shelf, views).
        $auraNew = number_format($totalPoints / (4 * $basePoints), 2);

        // Check when writing was posted (in days)
        $postedAt = Carbon::parse($this->created_at)->diffInDays();

        // Check if writing is awarded
        $awarded = isset($this->home_posted_at);

        // Persist to the database
        if ($auraNew >= $auraHome && $postedAt <= 31 && ! $awarded) {
            DB::table($this->getTable())->whereId($this->id)->update([
                'aura' => $auraNew,
                'aura_updated_at' => Carbon::now(),
                'home_posted_at' => Carbon::now(),
            ]);

            $this->author->notify(new WritingFeatured($this));
        } else {
            DB::table($this->getTable())->whereId($this->id)->update([
                'aura' => $auraNew,
                'aura_updated_at' => Carbon::now(),
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
            $path = '/storage/'.$this->extra_info['cover'];

            if (is_file(public_path($path))) {
                return $path;
            }
        }
    }

    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'complainable');
    }

    /**
     * Exclude writings authored by any of the given blocked user ids.
     *
     * @param  array<int>  $blockedUserIds
     */
    public function scopeVisibleTo(Builder $query, array $blockedUserIds): Builder
    {
        return $query->whereNotIn('user_id', $blockedUserIds);
    }

    /**
     * Shared sort used by every writings listing. 'popular' and 'likes'
     * break ties by aura (desc) so that, among writings with an identical
     * views/likes count, the higher-quality (higher-aura) one surfaces
     * first.
     */
    public function scopeSorted(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'popular' => $query->orderBy('views', 'desc')->orderBy('aura', 'desc'),
            'likes' => $query->orderBy('likes_count', 'desc')->orderBy('aura', 'desc'),
            default => $query->latest(),
        };
    }

    /**
     * Counts and author summary eager-loaded by every writings listing.
     */
    public function scopeWithListingRelations(Builder $query): Builder
    {
        return $query->withCount(['likes', 'comments', 'shelf'])
            ->with(['author' => function ($query): void {
                $query->select('id', 'username', 'name', 'karma', 'extra_info->avatar AS avatar');
            }]);
    }
}
