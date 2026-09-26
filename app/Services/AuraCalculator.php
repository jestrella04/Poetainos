<?php

namespace App\Services;

use App\Models\Shelf;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingFeatured;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Turns activity counts into the aura score of users and writings, and the
 * karma grade of users. Point weights come from the site settings.
 */
class AuraCalculator
{
    private const FEATURED_MAX_AGE_DAYS = 31;

    private const KARMA_WINDOW_DAYS = 90;

    private const LOWEST_KARMA = 'F';

    /**
     * Each count a user's aura is made of, mapped to its key under `aura.points.user` in the site settings.
     *
     * @var array<string, string>
     */
    private const USER_POINT_KEYS = [
        'writings' => 'writing',
        'likes' => 'like',
        'comments' => 'comment',
        'shelf' => 'shelf',
        'views' => 'views',
        'awards' => 'award',
    ];

    /**
     * Each count a writing's aura is made of, mapped to its key under `aura.points.writing` in the site settings.
     *
     * @var array<string, string>
     */
    private const WRITING_POINT_KEYS = [
        'likes' => 'like',
        'comments' => 'comment',
        'shelf' => 'shelf',
        'views' => 'views',
    ];

    /**
     * Minimum total points for each karma grade, highest first.
     *
     * @var array<int, string>
     */
    private const KARMA_GRADES = [
        4000 => 'A',
        3000 => 'B',
        2000 => 'C',
        1000 => 'D',
    ];

    public function updateUserAura(User $user): void
    {
        $this->updateUserAuras($user->id);
    }

    /**
     * Bring every user's aura up to date in a single statement.
     */
    public function updateAllUserAura(): void
    {
        $this->updateUserAuras(null);
    }

    public function updateUserKarma(User $user): void
    {
        $since = Carbon::now()->subDays(self::KARMA_WINDOW_DAYS)->startOfDay();

        $points = $this->score('user', self::USER_POINT_KEYS, [
            'likes' => $user->likes()->where('created_at', '>=', $since)->count(),
            'comments' => $user->comments()->where('created_at', '>=', $since)->count(),
            'shelf' => Shelf::where('user_id', $user->id)->where('created_at', '>=', $since)->count(),
            'awards' => $user->writings()->where('home_posted_at', '>=', $since)->count(),
        ]);

        $karma = $this->karmaGrade($points['total']);

        // A query update, like the aura's, so the nightly run doesn't touch every user's updated_at
        DB::table('users')->where('id', $user->id)->update(['karma' => $karma]);

        $user->karma = $karma;
        $user->syncOriginalAttribute('karma');
    }

    public function updateWritingAura(Writing $writing): void
    {
        $this->updateWritingAuras($writing->id);
    }

    /**
     * Bring every writing's aura up to date in a single statement, and
     * feature on the home page the recent ones it lifted over the minimum.
     */
    public function updateAllWritingAura(): void
    {
        $this->updateWritingAuras(null);
    }

    /**
     * The aura score of a scope (`user` or `writing`) from its counts. A count
     * that isn't given counts as zero.
     *
     * @param  array<string, string>  $pointKeys
     * @param  array<string, int|float>  $counts
     * @return array{base: int|float, total: int|float, score: float}
     */
    private function score(string $scope, array $pointKeys, array $counts): array
    {
        $countables = [];

        foreach (array_keys($pointKeys) as $name) {
            $countables[$name] = $counts[$name] ?? 0;
        }

        return $this->weightedScore($countables, $this->weights($scope, $pointKeys));
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
    public function weightedScore(array $countables, array $weights): array
    {
        $base = array_sum($weights);
        $total = 0;

        foreach ($countables as $key => $count) {
            $total += ($weights[$key] ?? 0) * $count;
        }

        $divisor = count($countables) * $base;
        $score = $divisor > 0 ? round($total / $divisor, 2) : 0.0;

        return [
            'base' => $base,
            'total' => $total,
            'score' => $score,
        ];
    }

    private function karmaGrade(int|float $totalPoints): string
    {
        foreach (self::KARMA_GRADES as $minimum => $grade) {
            if ($totalPoints >= $minimum) {
                return $grade;
            }
        }

        return self::LOWEST_KARMA;
    }

    /**
     * Update the aura of one user, or of all of them when no id is given.
     */
    private function updateUserAuras(?int $userId): void
    {
        $this->updateAuras('users', 'user', self::USER_POINT_KEYS, [
            'writings' => ['(select count(*) from writings where writings.user_id = users.id)', []],
            'likes' => ['(select count(*) from likes where likes.user_id = users.id)', []],
            'comments' => ['(select count(*) from comments where comments.user_id = users.id)', []],
            'shelf' => ['(select count(*) from shelves where shelves.user_id = users.id)', []],
            'views' => ['users.profile_views', []],
            'awards' => ['(select count(*) from writings where writings.user_id = users.id and writings.home_posted_at is not null)', []],
        ], $userId);
    }

    /**
     * Update the aura of one writing, or of all of them when no id is given,
     * then feature the ones that now qualify.
     */
    private function updateWritingAuras(?int $writingId): void
    {
        $isUpdated = $this->updateAuras('writings', 'writing', self::WRITING_POINT_KEYS, [
            'likes' => ['(select count(*) from likes where likes.likeable_type = ? and likes.likeable_id = writings.id)', [Writing::class]],
            'comments' => ['(select count(*) from comments where comments.writing_id = writings.id)', []],
            'shelf' => ['(select count(*) from shelves where shelves.writing_id = writings.id)', []],
            'views' => ['writings.views', []],
        ], $writingId);

        if ($isUpdated === true) {
            $this->featureQualifyingWritings($writingId);
        }
    }

    /**
     * Set the aura of a table's rows (one row, or all when no id is given) to
     * the same weighted score weightedScore() computes, from counts taken in
     * SQL. Nothing changes, and false is returned, without any weight configured.
     *
     * @param  array<string, string>  $pointKeys
     * @param  array<string, array{0: string, 1: array<int, mixed>}>  $counts  Each countable's SQL expression and its bindings.
     */
    private function updateAuras(string $table, string $scope, array $pointKeys, array $counts, ?int $id): bool
    {
        $weights = $this->weights($scope, $pointKeys);
        $base = array_sum($weights);

        if ($base <= 0) {
            return false;
        }

        $terms = [];
        $bindings = [];

        foreach ($counts as $name => [$countSql, $countBindings]) {
            $terms[] = '? * '.$countSql;
            $bindings = [...$bindings, $weights[$name], ...$countBindings];
        }

        $sql = "update {$table} set aura = round((".implode(' + ', $terms).') / cast(? as double), 2), aura_updated_at = ?';
        $bindings = [...$bindings, count($counts) * $base, Carbon::now()];

        if ($id !== null) {
            $sql .= ' where id = ?';
            $bindings[] = $id;
        }

        DB::update($sql, $bindings);

        return true;
    }

    /**
     * The point weight of each countable of a scope (`user` or `writing`); an unset weight counts as zero.
     *
     * @param  array<string, string>  $pointKeys
     * @return array<string, float>
     */
    private function weights(string $scope, array $pointKeys): array
    {
        return array_map(
            fn (string $pointKey): float => (float) (getSiteConfig("aura.points.{$scope}.{$pointKey}") ?? 0),
            $pointKeys,
        );
    }

    /**
     * Feature on the home page, and announce to their authors, the writings
     * (one, or all when no id is given) young and well-rated enough that
     * haven't been featured yet.
     */
    private function featureQualifyingWritings(?int $writingId): void
    {
        $minimumAura = getSiteConfig('aura.min_at_home');

        if ($minimumAura === null) {
            return;
        }

        Writing::with('author')
            ->whereNull('home_posted_at')
            ->where('created_at', '>=', Carbon::now()->subDays(self::FEATURED_MAX_AGE_DAYS))
            ->where('aura', '>=', $minimumAura)
            ->when($writingId !== null, fn ($query) => $query->whereKey($writingId))
            ->each(function (Writing $writing): void {
                if ($this->awardHome($writing) === true) {
                    $writing->author?->notify(new WritingFeatured($writing));
                }
            });
    }

    /**
     * Mark the writing as featured on the home page, once. Returns whether
     * this call was the one that did it, so only one caller announces it.
     */
    private function awardHome(Writing $writing): bool
    {
        return DB::table('writings')
            ->where('id', $writing->id)
            ->whereNull('home_posted_at')
            ->update(['home_posted_at' => Carbon::now()]) === 1;
    }
}
