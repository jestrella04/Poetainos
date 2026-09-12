<?php

namespace App\Models;

use App\Notifications\WritingFeatured;
use Carbon\Carbon;
use Database\Factories\WritingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class Writing extends Model
{
    /** @use HasFactory<WritingFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
     * @var array<string, string>
     */
    protected $casts = [
        'extra_info' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function path(): string
    {
        return route('writings.show', $this->slug);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function mainCategory(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->whereNull('parent_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function altCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->whereNotNull('parent_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function excerpt(): string
    {
        $len = mb_strlen($this->text);

        if ($len < 400) {
            return $this->text;
        }

        return mb_substr($this->text, 0, 400).'...';
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return MorphMany<Like, $this>
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * @return Collection<int, User>
     */
    public function likers(): Collection
    {
        $likes = $this->likes()->pluck('user_id');

        return User::forAuthorSummary()->whereIn('id', $likes)->get();
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function categoriesAsString(string $delimiter = ', '): string
    {
        $array = $this->categories
            ->map(function ($category) {
                return $category->name;
            })
            ->toArray();

        return implode($delimiter, $array);
    }

    public function tagsAsString(string $delimiter = ', '): string
    {
        $array = $this->tags
            ->map(function ($tag) {
                return $tag->name;
            })
            ->toArray();

        return implode($delimiter, $array);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function shelf(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shelves');
    }

    public function incrementViews(): void
    {
        DB::table($this->getTable())->where('id', $this->id)->increment('views');
    }

    public function updateAura(): void
    {
        // What's the minimum to be featured at home?
        $auraHome = getSiteConfig('aura.min_at_home');

        // Count user content
        $writing = Writing::where('id', $this->id)->withCount(['likes', 'comments', 'shelf'])->firstOrFail();
        $countables = [
            'likes' => $writing->likes_count,
            'comments' => $writing->comments_count,
            'shelf' => $writing->shelf_count,
            'views' => $this->views,
        ];

        // Get points from settings
        $weights = [
            'likes' => getSiteConfig('aura.points.writing.like'),
            'comments' => getSiteConfig('aura.points.writing.comment'),
            'shelf' => getSiteConfig('aura.points.writing.shelf'),
            'views' => getSiteConfig('aura.points.writing.views'),
        ];

        $points = calculateWeightedAuraScore($countables, $weights);

        // Do the math
        if ($points['base'] <= 0) {
            return;
        }

        $auraNew = $points['score'];

        // Check when writing was posted (in days)
        $postedAt = Carbon::parse($this->created_at)->diffInDays();

        // Check if writing is awarded
        $awarded = isset($this->home_posted_at);

        // Persist to the database
        if ($auraNew >= $auraHome && $postedAt <= 31 && ! $awarded) {
            DB::table($this->getTable())->where('id', $this->id)->update([
                'aura' => $auraNew,
                'aura_updated_at' => Carbon::now(),
                'home_posted_at' => Carbon::now(),
            ]);

            $this->author?->notify(new WritingFeatured($this));
        } else {
            DB::table($this->getTable())->where('id', $this->id)->update([
                'aura' => $auraNew,
                'aura_updated_at' => Carbon::now(),
            ]);
        }
    }

    public function externalLink(): ?string
    {
        if (! empty($this->extra_info['link'])) {
            return $this->extra_info['link'];
        }

        return null;
    }

    public function coverPath(): ?string
    {
        if (! empty($this->extra_info['cover'])) {
            $path = '/storage/'.$this->extra_info['cover'];

            if (is_file(public_path($path))) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return MorphMany<Complaint, $this>
     */
    public function complaints(): MorphMany
    {
        return $this->morphMany(Complaint::class, 'complainable');
    }

    /**
     * Exclude writings authored by any of the given blocked user ids.
     *
     * @param  Builder<Writing>  $query
     * @param  array<int>  $blockedUserIds
     * @return Builder<Writing>
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
     *
     * @param  Builder<Writing>  $query
     * @return Builder<Writing>
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
     *
     * @param  Builder<Writing>  $query
     * @return Builder<Writing>
     */
    public function scopeWithListingRelations(Builder $query): Builder
    {
        return $query->withCount(['likes', 'comments', 'shelf'])
            ->with(['author' => function ($query): void {
                $query->forAuthorSummary(withKarma: true);
            }]);
    }
}
