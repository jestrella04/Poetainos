<?php

namespace App\Models;

use App\Models\Concerns\HidesBlockedAuthors;
use Closure;
use Database\Factories\WritingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
    use HasFactory, HidesBlockedAuthors;

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
    ];

    /**
     * How much of the text a listing shows before the reader opens the writing.
     */
    private const LISTING_EXCERPT_LENGTH = 400;

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'listing_excerpt',
        'cover_url',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var list<string>
     */
    protected $hidden = [
        // Superseded by the cover and link columns; kept only until its data is verified and dropped
        'extra_info',
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
        return randomSample(User::forAuthorSummary()->whereIn('id', $this->likes()->select('user_id')), $limit);
    }

    /**
     * A random writing by a random author, so prolific authors don't crowd
     * out the rest, leaving out the given writings.
     *
     * @param  array<int, int>  $excludedIds
     *
     * @throws ModelNotFoundException<Writing> when no writing is left to pick.
     */
    public static function randomByRandomAuthor(array $excludedIds = []): self
    {
        $authorId = self::whereNotIn('id', $excludedIds)->distinct()->pluck('user_id')->shuffle()->first();

        $writing = $authorId === null
            ? null
            : randomSample(self::whereNotIn('id', $excludedIds)->where('user_id', $authorId), 1)->first();

        if ($writing === null) {
            throw (new ModelNotFoundException)->setModel(self::class);
        }

        return $writing;
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

    /**
     * The start of the text on a single line, cut at a word boundary when longer than the limit.
     */
    public function excerpt(int $maxLength = 200): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $this->text) ?? $this->text);

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $cut = mb_substr($text, 0, $maxLength);
        $lastSpace = mb_strrpos($cut, ' ');

        return rtrim($lastSpace === false ? $cut : mb_substr($cut, 0, $lastSpace), ' ,.;:').'…';
    }

    /**
     * The start of the text shown in writing listings; null when the text wasn't selected.
     *
     * @return Attribute<?string, never>
     */
    protected function listingExcerpt(): Attribute
    {
        return Attribute::get(
            fn (): ?string => array_key_exists('text', $this->attributes) ? $this->excerpt(self::LISTING_EXCERPT_LENGTH) : null,
        );
    }

    /**
     * The absolute URL of the cover image; null when there is none or the cover wasn't selected.
     *
     * @return Attribute<?string, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => array_key_exists('cover', $this->attributes) ? storageUrl($this->cover) : null,
        );
    }

    public function incrementViews(): void
    {
        DB::table($this->getTable())->where('id', $this->id)->increment('views');

        $this->views++;
        $this->syncOriginalAttribute('views');
    }

    /**
     * @return MorphMany<Complaint, $this>
     */
    public function complaints(): MorphMany
    {
        return $this->morphMany(Complaint::class, 'complainable');
    }

    /**
     * Shared sort used by every writings listing. 'popular' and 'likes'
     * break ties by aura (desc) so that, among writings with an identical
     * views/likes count, the higher-quality (higher-aura) one surfaces
     * first. The id breaks any remaining tie, so paginated pages never
     * repeat or skip a writing. 'likes' orders by the `likes_count` that
     * withListingRelations() adds, so it must be applied first.
     *
     * @param  Builder<Writing>  $query
     * @return Builder<Writing>
     */
    public function scopeSorted(Builder $query, string $sort): Builder
    {
        $sorted = match ($sort) {
            'popular' => $query->orderBy('views', 'desc')->orderBy('aura', 'desc'),
            'likes' => $query->orderBy('likes_count', 'desc')->orderBy('aura', 'desc'),
            default => $query->latest(),
        };

        return $sorted->orderBy($query->getModel()->qualifyColumn('id'), 'desc');
    }

    /**
     * Counts, author summary and the viewer's reactions eager-loaded by every writings listing.
     *
     * @param  Builder<Writing>  $query
     * @return Builder<Writing>
     */
    public function scopeWithListingRelations(Builder $query): Builder
    {
        return $query->withCount(['likes', 'comments', 'shelf'])
            ->withExists(self::viewerReactions())
            ->with(['author' => function ($query): void {
                $query->forAuthorSummary(withKarma: true);
            }]);
    }

    /**
     * Whether the signed-in viewer liked (`is_liked`) and shelved
     * (`is_shelved`) each writing, as `withExists()`/`loadExists()`
     * relations. Nothing for guests, who have no reactions.
     *
     * @return array<string, Closure>
     */
    public static function viewerReactions(): array
    {
        $viewerId = auth()->guard()->id();

        if ($viewerId === null) {
            return [];
        }

        return [
            'likes as is_liked' => function ($query) use ($viewerId): void {
                $query->where('user_id', $viewerId);
            },
            'shelf as is_shelved' => function ($query) use ($viewerId): void {
                $query->where('shelves.user_id', $viewerId);
            },
        ];
    }
}
