<?php

use App\Models\Writing;

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

describe('escapeLike', function (): void {
    it('escapes the characters that are wildcards in a LIKE pattern', function (string $input, string $expected): void {
        // Then
        expect(escapeLike($input))->toBe($expected);
    })->with([
        'a percent sign' => ['50%', '50\%'],
        'an underscore' => ['a_b', 'a\_b'],
        'a backslash' => ['a\b', 'a\\\\b'],
        'plain text' => ['poetry', 'poetry'],
    ]);
});

describe('slugify', function (): void {
    it('appends the first free number when the slug is taken', function (): void {
        // Given
        Writing::factory()->create(['slug' => 'my-poem']);
        Writing::factory()->create(['slug' => 'my-poem-1']);

        // When
        $slug = slugify('writings', 'My Poem');

        // Then
        expect($slug)->toBe('my-poem-2');
    });

    it('keeps finding a free slug beyond ten duplicates', function (): void {
        // Given
        Writing::factory()->create(['slug' => 'twin']);
        foreach (range(1, 12) as $number) {
            Writing::factory()->create(['slug' => "twin-{$number}"]);
        }

        // When
        $slug = slugify('writings', 'Twin');

        // Then
        expect($slug)->toBe('twin-13');
    });

    it('never returns a slug that a static route would swallow', function (string $title, string $expected): void {
        // Then
        expect(slugify('writings', $title))->toBe($expected);
    })->with([
        'random' => ['Random', 'random-1'],
        'awards' => ['Awards', 'awards-1'],
        'create' => ['Create', 'create-1'],
        'an ordinary title' => ['Autumn', 'autumn'],
    ]);
});

describe('isTruthy', function (): void {
    it('recognises the values forms and settings use for "on"', function (mixed $value, bool $expected): void {
        // Then
        expect(isTruthy($value))->toBe($expected);
    })->with([
        'the string 1' => ['1', true],
        'the integer 1' => [1, true],
        'true' => [true, true],
        'the word true' => ['true', true],
        'ON in capitals' => ['ON', true],
        'yes' => ['yes', true],
        'the string 0' => ['0', false],
        'false' => [false, false],
        'null' => [null, false],
        'an empty string' => ['', false],
        'the word off' => ['off', false],
        'anything else' => ['maybe', false],
    ]);
});

describe('isSafeRedirectPath', function (): void {
    it('only accepts same-site relative paths', function (?string $url, bool $expected): void {
        // Then
        expect(isSafeRedirectPath($url))->toBe($expected);
    })->with([
        'a path' => ['/writings/create', true],
        'null' => [null, false],
        'an empty string' => ['', false],
        'an absolute URL' => ['https://evil.example', false],
        'a protocol relative URL' => ['//evil.example', false],
        'a path that smuggles a scheme' => ['/redirect?to=https://evil.example', false],
        'a relative path without a slash' => ['writings', false],
    ]);
});
