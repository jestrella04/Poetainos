<?php

use App\Models\Writing;
use Illuminate\Support\Str;

describe('resolveSort', function (): void {
    it('returns the requested sort when it is in the whitelist', function (): void {
        // Given
        request()->merge(['sort' => 'popular']);

        // Then
        expect(resolveSort(['latest', 'popular']))->toBe('popular');
    });

    it('falls back to the default when the sort is not whitelisted', function (): void {
        // Given
        request()->merge(['sort' => fake()->lexify('sort-????')]);
        $default = fake()->word();

        // Then
        expect(resolveSort(['latest', 'popular']))->toBe('latest');
        expect(resolveSort(['latest', 'popular'], $default))->toBe($default);
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
        $title = fakeTitle();
        $takenSlug = Str::slug($title);
        Writing::factory()->create(['slug' => $takenSlug]);
        Writing::factory()->create(['slug' => "{$takenSlug}-1"]);

        // When
        $slug = slugify('writings', $title);

        // Then
        expect($slug)->toBe("{$takenSlug}-2");
    });

    it('keeps finding a free slug beyond ten duplicates', function (): void {
        // Given
        $title = fakeTitle();
        $takenSlug = Str::slug($title);
        $duplicates = fake()->numberBetween(11, 20);
        Writing::factory()->create(['slug' => $takenSlug]);
        foreach (range(1, $duplicates) as $number) {
            Writing::factory()->create(['slug' => "{$takenSlug}-{$number}"]);
        }

        // When
        $slug = slugify('writings', $title);

        // Then
        expect($slug)->toBe($takenSlug.'-'.($duplicates + 1));
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
