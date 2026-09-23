<?php

use App\Services\AuraCalculator;

/**
 * @param  list<string>  $keys
 * @return array<string, int>
 */
function fakeCountables(array $keys): array
{
    return collect($keys)->mapWithKeys(fn (string $key): array => [$key => fake()->numberBetween(0, 500)])->all();
}

/**
 * @param  list<string>  $keys
 * @return array<string, int>
 */
function fakeWeights(array $keys): array
{
    return collect($keys)->mapWithKeys(fn (string $key): array => [$key => fake()->numberBetween(1, 20)])->all();
}

describe('weightedScore', function (): void {
    it('matches the writing aura formula for any set of countables', function (): void {
        // Given
        $countables = fakeCountables(['likes', 'comments', 'shelf', 'views']);
        $weights = fakeWeights(['likes', 'comments', 'shelf', 'views']);

        // When
        $points = app(AuraCalculator::class)->weightedScore($countables, $weights);

        // Then
        $basePoints = $weights['likes'] + $weights['comments'] + $weights['shelf'] + $weights['views'];
        $totalPoints = ($weights['likes'] * $countables['likes'])
            + ($weights['comments'] * $countables['comments'])
            + ($weights['shelf'] * $countables['shelf'])
            + ($weights['views'] * $countables['views']);
        $expectedScore = round($totalPoints / (4 * $basePoints), 2);

        expect($points['base'])->toBe($basePoints);
        expect($points['total'])->toBe($totalPoints);
        expect($points['score'])->toBe($expectedScore);
    });

    it('matches the user aura formula for any set of countables', function (): void {
        // Given
        $countables = fakeCountables(['writings', 'likes', 'comments', 'shelf', 'views', 'awards']);
        $weights = fakeWeights(['writings', 'likes', 'comments', 'shelf', 'views', 'awards']);

        // When
        $points = app(AuraCalculator::class)->weightedScore($countables, $weights);

        // Then
        $basePoints = $weights['writings'] + $weights['likes'] + $weights['comments']
            + $weights['shelf'] + $weights['views'] + $weights['awards'];
        $totalPoints = ($weights['writings'] * $countables['writings'])
            + ($weights['likes'] * $countables['likes'])
            + ($weights['comments'] * $countables['comments'])
            + ($weights['shelf'] * $countables['shelf'])
            + ($weights['views'] * $countables['views'])
            + ($weights['awards'] * $countables['awards']);
        $expectedScore = round($totalPoints / (6 * $basePoints), 2);

        expect($points['base'])->toBe($basePoints);
        expect($points['total'])->toBe($totalPoints);
        expect($points['score'])->toBe($expectedScore);
    });

    it('derives the divisor from the countable count automatically', function (): void {
        // Given
        $count = fake()->numberBetween(1, 1000);
        $countables = ['a' => $count, 'b' => $count];
        $weights = ['a' => 1, 'b' => 1];

        // When
        $twoCountables = app(AuraCalculator::class)->weightedScore($countables, $weights);

        $countables['c'] = $count;
        $weights['c'] = 1;
        $threeCountables = app(AuraCalculator::class)->weightedScore($countables, $weights);

        // Then
        // Adding a countable changes the divisor (count($countables) * base)
        // without any manual literal to update.
        expect($twoCountables['score'])->not->toBe($threeCountables['score']);
        expect($twoCountables['score'])->toBe(round(2 * $count / (2 * 2), 2));
        expect($threeCountables['score'])->toBe(round(3 * $count / (3 * 3), 2));
    });

    it('keeps scores of a thousand or more intact', function (): void {
        // Given
        $views = fake()->numberBetween(4000, 1000000);

        // When
        $points = app(AuraCalculator::class)->weightedScore(['views' => $views], ['views' => 1]);

        // Then
        expect($points['score'])->toBe((float) $views);
    });

    it('returns a zero score when the base is zero', function (): void {
        // When
        $points = app(AuraCalculator::class)->weightedScore(['likes' => fake()->numberBetween(1, 1000)], ['likes' => 0]);

        // Then
        expect($points['base'])->toBe(0);
        expect($points['score'])->toBe(0.0);
    });
});
