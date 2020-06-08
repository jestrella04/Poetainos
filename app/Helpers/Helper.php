<?php

use App\Comment;
use App\Reply;
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

function createSlug($table, $title, $column = 'slug') {
    // Normalize the title
    $slug = Str::of($title)->slug('-');

    // Get any slug that could possibly be related.
    // This cuts the queries down by doing it once.
    $allSlugs = getRelatedSlugs($table, $slug);

    // If we haven't used it before then we are all good.
    if (! $allSlugs->contains($column, $slug)){
        return $slug;
    }

    // Just append numbers like a savage until we find one not used.
    for ($i = 1; $i <= 10; $i++) {
        $newSlug = $slug . '-' . $i;

        if (! $allSlugs->contains($column, $newSlug)) {
            return $newSlug;
        }
    }

    throw new \Exception('Can not create a unique slug');
}

function getRelatedSlugs($table, $slug, $column = 'slug') {
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
