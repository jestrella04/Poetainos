<?php

describe('calculateWeightedAuraScore', function (): void {
    it('matches the writing aura formula for a fixed set of countables', function (): void {
        // Given
        $countables = [
            'likes' => 5,
            'comments' => 2,
            'shelf' => 1,
            'views' => 20,
        ];
        $weights = [
            'likes' => 2,
            'comments' => 3,
            'shelf' => 4,
            'views' => 1,
        ];

        // When
        $points = calculateWeightedAuraScore($countables, $weights);

        // Then
        $basePoints = 2 + 3 + 4 + 1;
        $totalPoints = (2 * 5) + (3 * 2) + (4 * 1) + (1 * 20);
        $expectedScore = (float) number_format($totalPoints / (4 * $basePoints), 2);

        expect($points['base'])->toBe($basePoints);
        expect($points['total'])->toBe($totalPoints);
        expect($points['score'])->toBe($expectedScore);
    });

    it('matches the user aura formula for a fixed set of countables', function (): void {
        // Given
        $countables = [
            'writings' => 3,
            'likes' => 5,
            'comments' => 2,
            'shelf' => 1,
            'views' => 20,
            'awards' => 1,
        ];
        $weights = [
            'writings' => 10,
            'likes' => 2,
            'comments' => 3,
            'shelf' => 4,
            'views' => 1,
            'awards' => 20,
        ];

        // When
        $points = calculateWeightedAuraScore($countables, $weights);

        // Then
        $basePoints = 10 + 2 + 3 + 4 + 1 + 20;
        $totalPoints = (10 * 3) + (2 * 5) + (3 * 2) + (4 * 1) + (1 * 20) + (20 * 1);
        $expectedScore = (float) number_format($totalPoints / (6 * $basePoints), 2);

        expect($points['base'])->toBe($basePoints);
        expect($points['total'])->toBe($totalPoints);
        expect($points['score'])->toBe($expectedScore);
    });

    it('derives the divisor from the countable count automatically', function (): void {
        // Given
        $countables = ['a' => 10, 'b' => 10];
        $weights = ['a' => 1, 'b' => 1];

        // When
        $twoCountables = calculateWeightedAuraScore($countables, $weights);

        $countables['c'] = 10;
        $weights['c'] = 1;
        $threeCountables = calculateWeightedAuraScore($countables, $weights);

        // Then
        // Adding a countable changes the divisor (count($countables) * base)
        // without any manual literal to update.
        expect($twoCountables['score'])->not->toBe($threeCountables['score']);
        expect($twoCountables['score'])->toBe(round(20 / (2 * 2), 2));
        expect($threeCountables['score'])->toBe(round(30 / (3 * 3), 2));
    });

    it('returns a zero score when the base is zero', function (): void {
        // When
        $points = calculateWeightedAuraScore(['likes' => 5], ['likes' => 0]);

        // Then
        expect($points['base'])->toBe(0);
        expect($points['score'])->toBe(0.0);
    });
});

describe('resolveSort', function (): void {
    it('returns the requested sort when it is in the whitelist', function (): void {
        // Given
        request()->merge(['sort' => 'popular']);

        // Then
        expect(resolveSort(['latest', 'popular']))->toBe('popular');
    });

    it('falls back to the default when the sort is not whitelisted', function (): void {
        // Given
        request()->merge(['sort' => 'not-a-real-sort']);

        // Then
        expect(resolveSort(['latest', 'popular']))->toBe('latest');
        expect(resolveSort(['latest', 'popular'], 'featured'))->toBe('featured');
    });
});
