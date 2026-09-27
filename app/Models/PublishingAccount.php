<?php

namespace App\Models;

use Database\Factories\PublishingAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The site's own account on a social network it publishes to, with the
 * access token the API expects. Not a user's login: see SocialAccount.
 *
 * @mixin IdeHelperPublishingAccount
 */
class PublishingAccount extends Model
{
    /** @use HasFactory<PublishingAccountFactory> */
    use HasFactory;

    public const THREADS = 'threads';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'account_id',
        'access_token',
        'expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * The site's Threads account, if one was connected.
     */
    public static function threads(): ?self
    {
        return self::where('provider', self::THREADS)->first();
    }
}
