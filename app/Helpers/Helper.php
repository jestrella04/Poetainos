<?php

use App\Comment;
use App\Reply;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function getSiteConfig($path = '') {
    if (! empty($path)) {
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

function getSocialLink($user, $network) {
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

function slugify($table, $title, $column = 'slug', $separator = '-') {
    // Normalize the title
    $slug = Str::of($title)->slug($separator);

    // Get any slug that could possibly be related.
    // This cuts the queries down by doing it once.
    $allSlugs = getRelatedIdentifiers($table, $slug, $column);

    // If we haven't used it before then we are all good.
    if (! $allSlugs->contains($column, $slug)){
        return $slug;
    }

    // Just append numbers like a savage until we find one not used.
    for ($i = 1; $i <= 10; $i++) {
        $newSlug = $slug . $separator . $i;

        if (! $allSlugs->contains($column, $newSlug)) {
            return $newSlug;
        }
    }

    throw new \Exception('Can not create a unique slug');
}

function getRelatedIdentifiers($table, $slug, $column) {
    return DB::table($table)
        ->select($column)
        ->where($column, 'like', $slug . '%')
        ->get();
}

function getWritingCounter($writing) {
    return [
        'likes' => ReadableHumanNumber($writing->votes->where('vote', '>', 0)->count()),
        //'dislikes' => ReadableHumanNumber($writing->votes->where('vote', 0)->count()),
        'comments' => ReadableHumanNumber($writing->comments->count()),
        'replies' => Reply::whereIn('comment_id', Comment::where('writing_id', $writing->id)->pluck('id')->toArray())->count(),
        'views' => ReadableHumanNumber($writing->views),
        'shelf' => ReadableHumanNumber($writing->shelf->count()),
        'aura' => number_format($writing->aura, 2),
    ];
}

function getUserCounter($user) {
    return [
        'writings' => ReadableHumanNumber($user->writings()->count()),
        'flowers' => ReadableHumanNumber($user->writings()->whereNotNull('home_posted_at')->count()),
        'comments' => ReadableHumanNumber($user->comments()->count()),
        'replies' => ReadableHumanNumber($user->replies()->count()),
        'votes' => ReadableHumanNumber($user->votes()->count()),
        'views' => ReadableHumanNumber($user->profile_views ),
        'shelf' => ReadableHumanNumber($user->shelf()->count()),
        'hood' => ReadableHumanNumber($user->hood()->count()),
        'extendedHood' => ReadableHumanNumber($user->fellowHood($count = true)),
        'aura' => number_format($user->aura, 2),
    ];
}

function getUserAvatar(User $user, $size = 'md') {
    if (! empty($user->avatarPath())) {
        return '<img class="avatar avatar-'. $size .'" src="'. e($user->avatarPath()) .'" alt="'. e($user->getName()) .'" loading="lazy">' . PHP_EOL;
    } else {
        return '<span class="avatar avatar-'. $size .'">'. e($user->initials()) .'</span>' . PHP_EOL;
    }
}

function getNotificationMessage($notification) {
    switch ($notification->type) {
        case 'App\Notifications\WritingCommented':
            return __(':name has added a comment on your writing', ['name' => User::find($notification->data['user_id'])->getName()]);

        case 'App\Notifications\WritingFeatured':
            return __('Your writing has been awarded with a Golden Flower');

        case 'App\Notifications\WritingLiked':
            return __(':name has liked your writing', ['name' => User::find($notification->data['user_id'])->getName()]);

        case 'App\Notifications\WritingReplied':
            return __(':name has posted a reply to one of your comments', ['name' => User::find($notification->data['user_id'])->getName()]);

        case 'App\Notifications\WritingShelved':
            return __(':name has added your writing to his shelf', ['name' => User::find($notification->data['user_id'])->getName()]);
    }
}

function getPageTitle(Array $titleParts, $separator = '–') {
    $titleParts[] = getSiteConfig('name');
    $title = [];

    foreach($titleParts as $part) {
        $title[] = ucfirst($part);
    }

    unset($titleParts);
    return trim(implode(' ' . $separator . ' ', $title), ' ');
}

function linkify($string) {
    $pattern = '/\(?(?:(http|https):\\/\\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\\/]?[^\s\?]*[\\/]{1})*(?:\\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/';

    return preg_replace_callback($pattern, function($matches) {
        $emailPattern = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/';
        $isEmail = preg_match($emailPattern, $matches[0]) ? 'mailto:' : '';

        return '<a href="'. $isEmail . $matches[0] .'" target="_blank" title="'. $matches[0] .'">'. $matches[0] .'</a>';
    }, $string);
}
