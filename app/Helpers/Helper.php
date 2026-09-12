<?php

use App\Models\User;
use App\Notifications\CommentLiked;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use App\Notifications\WritingFeatured;
use App\Notifications\WritingLiked;
use App\Notifications\WritingShelved;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function getSiteConfig($path = '')
{
    if (! empty($path)) {
        $path = config('writerhood.'.$path);
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
    if (! $allSlugs->contains($column, $slug)) {
        return $slug;
    }

    // Just append numbers like a savage until we find one not used.
    for ($i = 1; $i <= 10; $i++) {
        $newSlug = $slug.$separator.$i;

        if (! $allSlugs->contains($column, $newSlug)) {
            return $newSlug;
        }
    }

    throw new Exception('Can not create a unique slug');
}

function getRelatedIdentifiers($table, $slug, $column)
{
    return DB::table($table)
        ->select($column)
        ->where($column, 'like', $slug.'%')
        ->get();
}

function getNotificationMessage($notification)
{
    switch ($notification->type) {
        case WritingCommented::class:
            $message = __(':name has added a comment on your writing', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        case WritingCommentMentioned::class:
            $message = __(':name has mentioned you in a comment', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        case WritingFeatured::class:
            $message = __('Your writing has been awarded with a Golden Flower');
            break;

        case WritingLiked::class:
            $message = __(':name has liked your writing', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        case WritingShelved::class:
            $message = __(':name has added your writing to his shelf', [
                'name' => User::find($notification->data['user_id'])->getName(),
            ]);
            break;

        case CommentLiked::class:
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

    if (! empty($string) && in_array($string, [1, '1', true, 'true', 'on', 'yes'], true)) {
        return true;
    }

    return false;
}

function hydrateSettings($text)
{
    return preg_replace_callback(
        '/{{([^}]+)}}/',
        fn ($matches) => getSiteConfig($matches[1]),
        $text
    );
}

function inRange($value, $min, $max)
{
    return $value >= $min && $value < $max;
}

/**
 * Weighted average "aura" score for a set of countable metrics (e.g. likes,
 * comments, shelf adds). Each countable's contribution is its raw count
 * multiplied by its per-unit weight; the base is the sum of the weights
 * themselves. The divisor is derived from the number of countables so that
 * adding a new countable can never desync the math with a stale literal.
 *
 * @param  array<string, int|float>  $countables  Raw counts keyed by metric name.
 * @param  array<string, int|float>  $weights  Per-unit point values keyed by the same metric names.
 * @return array{base: int|float, total: int|float, score: float}
 */
function calculateWeightedAuraScore(array $countables, array $weights): array
{
    $base = array_sum($weights);
    $total = 0;

    foreach ($countables as $key => $count) {
        $total += ($weights[$key] ?? 0) * $count;
    }

    $divisor = count($countables) * $base;
    $score = $divisor > 0 ? (float) number_format($total / $divisor, 2) : 0.0;

    return [
        'base' => $base,
        'total' => $total,
        'score' => $score,
    ];
}

/**
 * Resolve a request's `sort` value against a controller-specific whitelist,
 * falling back to a default when the value is missing or not allowed.
 *
 * @param  array<int, string>  $allowed
 */
function resolveSort(array $allowed, string $default = 'latest'): string
{
    return in_array(request('sort'), $allowed, true) ? request('sort') : $default;
}

function tailFile(string $path, int $lines = 100): string
{
    if (! is_readable($path)) {
        return '';
    }

    $file = new SplFileObject($path, 'r');
    $file->seek(PHP_INT_MAX);
    $lastLine = $file->key();

    $file->seek(max(0, $lastLine - $lines));

    $tail = [];

    while (! $file->eof()) {
        $tail[] = $file->fgets();
        $file->next();
    }

    return implode('', $tail);
}
