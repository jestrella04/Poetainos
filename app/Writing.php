<?php

namespace App;

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
        return substr($this->text, 0, 400);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function categoriesAsString($delimiter = ',')
    {
        $array = $this->categories
        ->map(function ($category) {
            return $category->name;
        })
        ->toArray();

        return implode($delimiter, $array);
    }

    public function tagsAsString($delimiter = ',')
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
        $upvotes = $this->votes->where('vote', '>', 0)->count();
        $downvotes = $this->votes->where('vote', 0)->count();
        $replies = Reply::whereIn('comment_id', Comment::where('writing_id', $this->id)->pluck('id')->toArray())->count();
        $comments = $this->comments->count() + $replies;
        $shelf = $this->shelf->count();
        $views = $this->views;

        // Get points from settings
        $pointsUpvotes = getSiteConfig('aura.points.writing.upvote');
        $pointsDownvotes = getSiteConfig('aura.points.writing.downvote');
        $pointsComments = getSiteConfig('aura.points.writing.comment');
        $pointsShelf = getSiteConfig('aura.points.writing.shelf');
        $pointsViews = getSiteConfig('aura.points.writing.views');
        $basePoints = $pointsUpvotes + $pointsDownvotes + $pointsComments + $pointsShelf + $pointsViews;

        // Calculate points as per settings
        $pointsUpvotes = $pointsUpvotes * $upvotes;
        $pointsDownvotes = $pointsDownvotes * $downvotes;
        $pointsComments = $pointsComments * $comments;
        $pointsShelf = $pointsShelf * $shelf;
        $pointsViews = $pointsViews * $views;
        $totalPoints = $pointsUpvotes + $pointsDownvotes + $pointsComments + $pointsShelf + $pointsViews;

        // Do the math
        $auraNew = ($totalPoints / $basePoints) * (1 + ($basePoints / 100));
        $auraNew = number_format($auraNew, 2);

        // Persist to the database
        if ($auraOld < $auraHome && $auraNew >= $auraHome) {
            DB::table($this->getTable())->whereId($this->id)->update([
                'aura' => $auraNew,
                'aura_updated_at' => Carbon::now(),
                'home_posted_at' => Carbon::now(),
            ]);
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
            $path = '/static/storage/' . $this->extra_info['cover'];

            if (is_file(public_path($path))) {
                return $path;
            }
        }
    }

    public function shareLinks()
    {
        $url = $this->path();
        $title = $this->title;
        $facebookBaseUrl = 'https://facebook.com/sharer/sharer.php?u={url}';
        $twitterBaseUrl = 'https://twitter.com/intent/tweet/?text={text}&amp;url={url}';
        $whatsappBaseUrl = 'whatsapp://send?text={text}%20{url}';
        $telegramBaseUrl = 'https://t.me/share/url?url={url}&text={text}';

        return [
            'facebook' => [
                'url' => str_replace('{url}', $url, $facebookBaseUrl),
                'class' => 'share-link facebook-link',
                'icon' => 'fab fa-fw fa-facebook',
            ],
            'twitter' => [
                'url' => str_replace('{text}', $title, str_replace('{url}', $url, $twitterBaseUrl)),
                'class' => 'share-link twitter-link',
                'icon' => 'fab fa-fw fa-twitter',
            ],
            'whatsapp' => [
                'url' => str_replace('{text}', $title, str_replace('{url}', $url, $whatsappBaseUrl)),
                'class' => 'share-link whatsapp-link',
                'icon' => 'fab fa-fw fa-whatsapp',
            ],
            'telegram' => [
                'url' => str_replace('{text}', $title, str_replace('{url}', $url, $telegramBaseUrl)),
                'class' => 'share-link telegram-link',
                'icon' => 'fab fa-fw fa-telegram',
            ],
            'copy link' => [
                'url' => $url,
                'class' => 'share-link copy-to-clipboard-link',
                'icon' => 'far fa-fw fa-clone',
            ],
        ];
    }
}
