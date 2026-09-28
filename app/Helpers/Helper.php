<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A site setting by dot path, or every setting when the path is empty. A
 * setting is stored as `{"description": …, "value": …}`; asking for it returns
 * just the value.
 */
function getSiteConfig(string $path = ''): mixed
{
    if ($path !== '') {
        $path = config('poetainos.'.$path);
    } else {
        $path = config('poetainos');
    }

    if (is_array($path) && Arr::exists($path, 'value')) {
        return $path['value'];
    } else {
        return $path;
    }
}

function slugify(string $table, string $title, string $column = 'slug', string $separator = '-'): string
{
    // Slugs that would be swallowed by a static route such as /writings/random
    $reserved = ['create', 'edit', 'delete', 'store', 'random', 'awards', 'query'];

    // Normalize the title
    $slug = Str::of($title)->slug($separator)->toString();

    // Get any slug that could possibly be related.
    // This cuts the queries down by doing it once.
    $usedSlugs = existingSlugsLike($table, $slug, $column)->pluck($column);

    $isTaken = fn (string $candidate): bool => in_array($candidate, $reserved, true)
        || $usedSlugs->contains($candidate);

    // If we haven't used it before then we are all good.
    if ($isTaken($slug) === false) {
        return $slug;
    }

    // Otherwise append the first number that is still free.
    $suffix = 1;

    while ($isTaken($slug.$separator.$suffix)) {
        $suffix++;
    }

    return $slug.$separator.$suffix;
}

/**
 * @return Collection<int, stdClass>
 */
function existingSlugsLike(string $table, string $slug, string $column): Collection
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
    return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
}

/**
 * Replace each `{{setting.path}}` placeholder in a text with that site
 * setting's value. A placeholder that names no single value is left as is.
 */
function interpolateSiteSettings(string $text): string
{
    return (string) preg_replace_callback(
        '/{{([^}]+)}}/',
        function (array $matches): string {
            $value = getSiteConfig($matches[1]);

            return is_scalar($value) ? (string) $value : $matches[0];
        },
        $text
    );
}

/**
 * Whether a post-login "redirect" target is a same-site relative path,
 * safe to hand to Redirect::setIntendedUrl(). Rejects absolute and
 * protocol-relative URLs so the value can't be used for an open redirect.
 */
function isSafeRedirectPath(?string $url): bool
{
    if ($url === null || $url === '') {
        return false;
    }

    if (str_starts_with($url, '//') || str_contains($url, '://')) {
        return false;
    }

    return str_starts_with($url, '/');
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
 * Up to `$take` random records of a query, in random order. The ids are
 * drawn from an id-only query and only the drawn rows are loaded, instead
 * of ORDER BY RAND(), which reads and sorts every matching row in full.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @param  Builder<TModel>|Relation<TModel, *, *>  $query  A query builder, or a relation (e.g. $user->writings()).
 * @return EloquentCollection<int, TModel>
 */
function randomSample(Builder|Relation $query, int $take): EloquentCollection
{
    $model = $query->getModel();
    $ids = (clone $query)->toBase()->pluck($model->getQualifiedKeyName())->shuffle()->take($take);

    return $query->whereKey($ids->all())->get()->shuffle();
}

/**
 * Random writings for a "related content" widget, with each one's author
 * summary eager-loaded (the shape every such widget needs).
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @param  Builder<TModel>|Relation<TModel, *, *>  $query  A query builder, or a relation (e.g. $user->writings()).
 * @return EloquentCollection<int, TModel>
 */
function randomWritingsWithAuthor(Builder|Relation $query, int $take = 5): EloquentCollection
{
    return randomSample($query->with(['author' => function ($authorQuery): void {
        $authorQuery->forAuthorSummary();
    }]), $take);
}

/**
 * Escape the characters that act as wildcards in a SQL LIKE pattern, so
 * user input matches literally.
 */
function escapeLike(string $value): string
{
    return addcslashes($value, '\\%_');
}

/**
 * The Ziggy route group (config/ziggy.php) the current user may see: admins
 * get every route, everyone else all but the admin panel's.
 */
function ziggyRouteGroup(): string
{
    return auth()->user()?->isAllowed('admin') === true ? 'admin' : 'visitor';
}
