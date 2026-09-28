<?php

namespace App\Models;

use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What a user tells others about themselves: avatar, bio, whereabouts and
 * their handles on other social networks.
 *
 * @mixin IdeHelperUserProfile
 */
class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    /**
     * The social networks a profile can link to, each with the longest handle it accepts.
     *
     * @var array<string, int>
     */
    public const SOCIAL_NETWORKS = [
        'twitter' => 250,
        'threads' => 250,
        'instagram' => 100,
        'facebook' => 250,
        'youtube' => 100,
        'goodreads' => 250,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'avatar',
        'bio',
        'website',
        'location',
        'occupation',
        'interests',
        'twitter',
        'threads',
        'instagram',
        'facebook',
        'youtube',
        'goodreads',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * @return Attribute<?string, never>
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => storageUrl($this->avatar));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The handles the user filled in, keyed by social network.
     *
     * @return array<string, string>
     */
    public function socialHandles(): array
    {
        return array_filter(
            $this->only(array_keys(self::SOCIAL_NETWORKS)),
            fn (mixed $handle): bool => is_string($handle) && $handle !== '',
        );
    }
}
