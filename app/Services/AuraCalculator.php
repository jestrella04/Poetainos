<?php

namespace App\Services;

use App\Models\Shelf;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingFeatured;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
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
        $counted = User::where('id', $user->id)
            ->withCount(['writings', 'likes', 'comments', 'shelf', 'awards'])
            ->firstOrFail();

        $points = $this->userPoints([
            'writings' => $counted->writings_count,
            'likes' => $counted->likes_count,
            'comments' => $counted->comments_count,
            'shelf' => $counted->shelf_count,
            'awards' => $counted->awards_count,
            'views' => $counted->profile_views,
        ]);

        // Without any weight configured there is nothing to score against
        if ($points['base'] <= 0) {
            return;
        }

        DB::table('users')->where('id', $user->id)->update([
            'aura' => $points['score'],
            'aura_updated_at' => Carbon::now(),
        ]);
    }

    public function updateUserKarma(User $user): void
    {
        $since = Carbon::now()->subDays(self::KARMA_WINDOW_DAYS)->startOfDay();

        $points = $this->userPoints([
            'likes' => $user->likes()->where('created_at', '>=', $since)->count(),
            'comments' => $user->comments()->where('created_at', '>=', $since)->count(),
            'shelf' => Shelf::where('user_id', $user->id)->where('created_at', '>=', $since)->count(),
            'awards' => $user->writings()->where('home_posted_at', '>=', $since)->count(),
        ]);

        $user->forceFill([
            'karma' => $this->karmaGrade($points['total']),
            'aura_updated_at' => Carbon::now(),
        ])->save();
    }

    public function updateWritingAura(Writing $writing): void
    {
        $counted = Writing::where('id', $writing->id)
            ->withCount(['likes', 'comments', 'shelf'])
            ->firstOrFail();

        $points = calculateWeightedAuraScore([
            'likes' => $counted->likes_count,
            'comments' => $counted->comments_count,
            'shelf' => $counted->shelf_count,
            'views' => $counted->views,
        ], [
            'likes' => getSiteConfig('aura.points.writing.like'),
            'comments' => getSiteConfig('aura.points.writing.comment'),
            'shelf' => getSiteConfig('aura.points.writing.shelf'),
            'views' => getSiteConfig('aura.points.writing.views'),
        ]);

        if ($points['base'] <= 0) {
            return;
        }

        $now = Carbon::now();
        $aura = ['aura' => $points['score'], 'aura_updated_at' => $now];
        $query = DB::table('writings')->where('id', $writing->id);

        if ($this->qualifiesForHome($points['score'], $counted) && $this->awardHome(clone $query, $aura, $now)) {
            $writing->author?->notify(new WritingFeatured($writing));

            return;
        }

        $query->update($aura);
    }

    /**
     * @param  array<string, int|float>  $counts
     * @return array{base: int|float, total: int|float, score: float}
     */
    private function userPoints(array $counts): array
    {
        return calculateWeightedAuraScore([
            'writings' => $counts['writings'] ?? 0,
            'likes' => $counts['likes'] ?? 0,
            'comments' => $counts['comments'] ?? 0,
            'shelf' => $counts['shelf'] ?? 0,
            'views' => $counts['views'] ?? 0,
            'awards' => $counts['awards'] ?? 0,
        ], [
            'writings' => getSiteConfig('aura.points.user.writing'),
            'likes' => getSiteConfig('aura.points.user.like'),
            'comments' => getSiteConfig('aura.points.user.comment'),
            'shelf' => getSiteConfig('aura.points.user.shelf'),
            'views' => getSiteConfig('aura.points.user.views'),
            'awards' => getSiteConfig('aura.points.user.award'),
        ]);
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

    private function qualifiesForHome(float $aura, Writing $writing): bool
    {
        return $aura >= getSiteConfig('aura.min_at_home')
            && Carbon::parse($writing->created_at)->diffInDays() <= self::FEATURED_MAX_AGE_DAYS;
    }

    /**
     * Mark the writing as featured on the home page, once. Returns whether
     * this call was the one that did it, so only one caller announces it.
     *
     * @param  array<string, mixed>  $aura
     */
    private function awardHome(Builder $query, array $aura, Carbon $now): bool
    {
        return $query->whereNull('home_posted_at')->update([...$aura, 'home_posted_at' => $now]) === 1;
    }
}
