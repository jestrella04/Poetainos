<?php

use App\Category;
use App\Tag;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function getPopularTags($count = 5) {
    return Tag::withCount('writings')->orderByDesc('writings_count')->take($count)->get();
}

function getPopularCategories($count = 5)
{
    return Category::withCount('writings')->orderByDesc('writings_count')->take($count)->get();
}

function getFeaturedAuthors($count = 5) {
    return User::orderByDesc('aura')->take($count)->get();
}

function getSiteConfig($path) {
    $path = config('hood.site.' . $path);

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
