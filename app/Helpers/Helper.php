<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function getSiteConfig(string $path = ''): mixed
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

function slugify(string $table, string $title, string $column = 'slug', string $separator = '-'): string
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

/**
 * @return Collection<int, stdClass>
 */
function getRelatedIdentifiers(string $table, string $slug, string $column): Collection
{
    return DB::table($table)
        ->select($column)
        ->where($column, 'like', $slug.'%')
        ->get();
}

/**
 * @param  array<int, string>  $titleParts
 */
function getPageTitle(array $titleParts, string $separator = '–'): string
{
    $titleParts[] = getSiteConfig('name');

    return implode(" {$separator} ", $titleParts);
}

function isTruthy(mixed $value): bool
{
    $value = strtolower((string) $value);

    if (! empty($value) && in_array($value, [1, '1', true, 'true', 'on', 'yes'], true)) {
        return true;
    }

    return false;
}

function hydrateSettings(string $text): string
{
    return (string) preg_replace_callback(
        '/{{([^}]+)}}/',
        fn ($matches) => getSiteConfig($matches[1]),
        $text
    );
}

function inRange(int|float $value, int|float $min, int|float $max): bool
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

/**
 * Random writings for a "related content" widget, with each one's author
 * summary eager-loaded (the shape every such widget needs).
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @param  Builder<TModel>|Relation<TModel, *, *>  $query  A query builder, or a relation (e.g. $user->writings()) — both proxy with()/inRandomOrder()/take()/get() to the underlying builder.
 * @return EloquentCollection<int, TModel>
 */
function randomWritingsWithAuthor(Builder|Relation $query, int $take = 5): EloquentCollection
{
    return $query
        ->with(['author' => function ($authorQuery): void {
            $authorQuery->forAuthorSummary();
        }])
        ->inRandomOrder()
        ->take($take)
        ->get();
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
