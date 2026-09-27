<?php

namespace App\Models;

use App\Models\Concerns\HidesBlockedAuthors;
use Closure;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin IdeHelperComment
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, HidesBlockedAuthors;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'writing_id',
        'message',
    ];

    /**
     * @return BelongsTo<Writing, $this>
     */
    public function writing(): BelongsTo
    {
        return $this->belongsTo(Writing::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return MorphMany<Like, $this>
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Whether the signed-in viewer liked (`is_liked`) each comment, as a
     * `withExists()` relation. Nothing for guests, who have no reactions.
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
        ];
    }
}
