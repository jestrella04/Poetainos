<?php

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        case 'App\Notifications\CommentLiked':
            $message = __(':name has liked your comment', [
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
    $titleParts[] = getSiteConfig(('name'));
    return implode(" {$separator} ", $titleParts);
}

function isTruthy($string)
{
    $string = strtolower($string);

    if (!empty($string) && in_array($string, [1, "1", true, "true", "on", "yes"], true)) {
        return true;
    }

    return false;
}
