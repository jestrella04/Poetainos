<?php

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Models\Shelf;
use App\Models\Like;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

function getSiteConfig($path = '')
{
    if (!empty($path)) {
        $path = config('writerhood.' . $path);
    } else {
        $path = config('writerhood');
    }

    if (is_array($path) && Arr::exists($path, 'value')) {
        return $path['value'];
    } else {
        return $path;
    }
}

function getSocialLink($user, $network)
{
    $url = '';

    switch ($network) {
        case 'twitter':
            $url = 'https://twitter.com/' . $user;
            break;

        case 'instagram':
            $url = 'https://instagram.com/' . $user;
            break;

        case 'facebook':
            $url = 'https://facebook.com/' . $user;
            break;

        case 'youtube':
            $url = 'https://youtube.com/user/' . $user;
            break;

        case 'goodreads':
            $url = 'https://www.goodreads.com/' . $user;
            break;

        default:
            $url = $user;
            break;
    }

    return $url;
}

function slugify($table, $title, $column = 'slug', $separator = '-')
{
    // Normalize the title
    $slug = Str::of($title)->slug($separator);

    // Get any slug that could possibly be related.
    // This cuts the queries down by doing it once.
    $allSlugs = getRelatedIdentifiers($table, $slug, $column);

    // If we haven't used it before then we are all good.
    if (!$allSlugs->contains($column, $slug)) {
        return $slug;
    }

    // Just append numbers like a savage until we find one not used.
    for ($i = 1; $i <= 10; $i++) {
        $newSlug = $slug . $separator . $i;

        if (!$allSlugs->contains($column, $newSlug)) {
            return $newSlug;
        }
    }

    throw new \Exception('Can not create a unique slug');
}

function getRelatedIdentifiers($table, $slug, $column)
{
    return DB::table($table)
        ->select($column)
        ->where($column, 'like', $slug . '%')
        ->get();
}

function getReadableNumber($number)
{
    if (is_numeric($number) && $number > 999) {
        return ReadableHumanNumber($number, $showDecimal = true, $decimals = 1);
    }

    return $number;
}

function getWritingCounter($writing)
{
    return [
        'likes' => [
            'counter' => $writing->likes()->count(),
            'readable' => getReadableNumber($writing->likes()->count()),
        ],
        'comments' => [
            'counter' => $writing->comments()->count(),
            'readable' => getReadableNumber($writing->comments()->count()),
        ],
        'views' => [
            'counter' => $writing->views,
            'readable' => getReadableNumber($writing->views),
        ],
        'shelf' => [
            'counter' => $writing->shelf()->count(),
            'readable' => getReadableNumber($writing->shelf()->count()),
        ],
        'aura' => [
            'counter' => $writing->aura,
            'readable' => intval($writing->aura),
        ]
    ];
}

function getUserCounter($user)
{
    return [
        'writings' => [
            'counter' => $user->writings()->count(),
            'readable' => getReadableNumber($user->writings()->count()),
        ],
        'flowers' => [
            'counter' => $user->writings()->whereNotNull('home_posted_at')->count(),
            'readable' => getReadableNumber($user->writings()->whereNotNull('home_posted_at')->count()),
        ],
        'comments' => [
            'counter' => $user->comments()->count(),
            'readable' => getReadableNumber($user->comments()->count()),
        ],
        'likes' => [
            'counter' => $user->likes()->count(),
            'readable' => getReadableNumber($user->likes()->count()),
        ],
        'views' => [
            'counter' => $user->profile_views,
            'readable' => getReadableNumber($user->profile_views),
        ],
        'shelf' => [
            'counter' => $user->shelf()->count(),
            'readable' => getReadableNumber($user->shelf()->count()),
        ],
        'hood' => [
            'counter' => $user->hood()->count(),
            'readable' => getReadableNumber($user->hood()->count()),
        ],
        'extendedHood' => [
            'counter' => $user->fellowHood($count = true),
            'readable' => getReadableNumber($user->fellowHood($count = true)),
        ],
        'aura' => [
            'counter' => $user->aura,
            'readable' => intval($user->aura),
        ]
    ];
}

function getUserAvatar(User $user, $size = 'md', $classList = [])
{
    $classList[] = 'avatar';
    $classList[] = 'avatar-' . $size;
    $classList = implode(' ', $classList);

    if (!empty($user->avatarPath())) {
        return '<img class="' . $classList . '" src="' . e($user->avatarPath()) . '" alt="' . e($user->getName()) . '" loading="lazy">' . PHP_EOL;
    } else {
        return '<span class="' . $classList . '">' . e($user->initials()) . '</span>' . PHP_EOL;
    }
}

function getNotificationMessage($notification)
{
    switch ($notification->type) {
        case 'App\Notifications\WritingCommented':
            $message = __(':name has added a comment on your writing', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        case 'App\Notifications\WritingCommentMentioned':
        case 'App\Notifications\WritingReplyMentioned':
            $message = __(':name has mentioned you in a comment', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        case 'App\Notifications\WritingFeatured':
            $message = __('Your writing has been awarded with a Golden Flower');
            break;

        case 'App\Notifications\WritingLiked':
            $message = __(':name has liked your writing', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        case 'App\Notifications\WritingReplied':
            $message = __(':name has posted a reply to one of your comments', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        case 'App\Notifications\WritingShelved':
            $message = __(':name has added your writing to his shelf', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        default:
            $message = false;
    }

    return $message;
}

function getPageTitle(array $titleParts, $separator = '–')
{
    $titleParts[] = getSiteConfig('name');
    $title = [];

    foreach ($titleParts as $part) {
        $title[] = ucfirst($part);
    }

    unset($titleParts);
    return trim(implode(' ' . $separator . ' ', $title), ' ');
}

function linkify($string)
{
    $pattern = '/\(?(?:(http|https):\\/\\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\\/]?[^\s\?]*[\\/]{1})*(?:\\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/';

    $string = preg_replace_callback($pattern, function ($matches) {
        $emailPattern = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/';
        $isEmail = preg_match($emailPattern, $matches[0]) ? 'mailto:' : '';

        return '<a href="' . $isEmail . $matches[0] . '" target="_blank" title="' . $isEmail . $matches[0] . '">' . cropify($matches[0]) . '</a>';
    }, $string);

    // Check for @mentions
    $mentionPattern = '/\B@[a-zA-Z0-9_-]+/';
    $string = preg_replace_callback($mentionPattern, function ($matches) {
        $user = User::where('username', '=', substr($matches[0], 1))->first();

        if (null !== $user) {
            return '<a href="' . $user->path() . '" title="' . $user->getName() . '">@' . $user->username  . '</a>';
        }

        return $matches[0];
    }, $string);

    return $string;
}

/*
 * Adapted from William Belle
 * https://github.com/williambelle/crop-url
 * Original code licensed under MPL license
 */
function cropify($url, $length = 40)
{
    if (strlen($url) <= $length) {
        return $url;
    }

    // Remove http:// or https://
    $url = preg_replace('/^https?:\/\//', '', $url);

    // Remove www.
    $url = preg_replace('/^www\./', '', $url);

    // Replace /foo/bar/foo/ with /…/…/…/
    $urlLength = strlen($url);

    while ($urlLength > $length) {
        $url = preg_replace('/(.*[^\/])\\/[^\/…]+\\/([^\/])/', '$1/…/$2', $url);

        if (strlen($url) === $urlLength) {
            break;
        } else {
            $urlLength = strlen($url);
        }
    }

    // Replace /…/…/…/ with /…/
    $url = preg_replace('/\/…\/(?:…\/)+/', '/…/', $url);

    // Replace all params except first
    while (strlen($url) > $length) {
        $idx = strrpos($url, '&');
        if ($idx === -1) {
            break;
        }

        $url = substr($url, 0, $idx) . '…';
    }

    // Replace first param
    if (strlen($url) > $length) {
        $idx = strrpos($url, '?');

        if ($idx !== -1) {
            $url = substr($url, 0, $idx) . '?…';
        }
    }

    // Replace endless hyphens
    while (strlen($url) > $length) {
        $idx = strrpos($url, '-');

        if ($idx === -1) {
            break;
        }

        $url = substr($url, 0, $idx) . '…';
    }

    return $url;
};

function isTruthy($string)
{
    $string = strtolower($string);

    if (!empty($string) && in_array($string, [1, "1", true, "true", "on", "yes"], true)) {
        return true;
    }

    return false;
}

function shareLinks($title, $url)
{
    $facebookBaseUrl = 'https://facebook.com/sharer/sharer.php?u={url}';
    $twitterBaseUrl = 'https://twitter.com/intent/tweet/?text={text}&url={url}';
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
        __('Copy link') => [
            'url' => $url,
            'class' => 'share-link copy-to-clipboard-link',
            'icon' => 'far fa-fw fa-clone',
        ],
    ];
}

function isWritingLiked(Writing $writing)
{
    if (auth()->check()) {
        $liked = Like::where('user_id', auth()->user()->id)
            ->whereHasMorph('likeable', [Writing::class], function (Builder $query) use ($writing) {
                $query->where('id', $writing->id);
            })->count();

        if ($liked > 0) {
            return true;
        }
    }

    return false;
}

function isCommentLiked(Comment $comment)
{
    if (auth()->check()) {
        $liked = Like::where('user_id', auth()->user()->id)
            ->whereHasMorph('likeable', [Comment::class], function (Builder $query) use ($comment) {
                $query->where('id', $comment->id);
            })->count();

        if ($liked > 0) {
            return true;
        }
    }

    return false;
}

function isWritingShelved(Writing $writing)
{
    if (auth()->check()) {
        $shelved = Shelf::where('user_id', auth()->user()->id)
            ->where('writing_id', $writing->id)
            ->count();

        if ($shelved > 0) {
            return true;
        }
    }

    return false;
}

function getCommentLikeCounter(Comment $comment)
{
    $count = Like::whereHasMorph('likeable', [Comment::class], function (Builder $query) use ($comment) {
        $query->where('id', $comment->id);
    })->count();

    return [
        'counter' => $count,
        'readable' => getReadableNumber($count),
    ];
}
