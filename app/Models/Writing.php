<?php

namespace App\Models;

use App\Services\AuraCalculator;
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

/**
 * @mixin IdeHelperWriting
 */
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
        'title',
        'slug',
        'text',
        'extra_info',
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
     * A random sample of the users who liked the writing.
     *
     * @return Collection<int, User>
     */
    public function likers(int $limit): Collection
    {
        return User::forAuthorSummary()
            ->whereIn('id', $this->likes()->select('user_id'))
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
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

        $this->views++;
        $this->syncOriginalAttribute('views');
    }

    public function updateAura(): void
    {
        app(AuraCalculator::class)->updateWritingAura($this);
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
        if ($blockedUserIds === []) {
            return $query;
        }

        return $query->whereNotIn($query->getModel()->qualifyColumn('user_id'), $blockedUserIds);
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
