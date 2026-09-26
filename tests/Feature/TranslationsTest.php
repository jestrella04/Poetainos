<?php

use Illuminate\Support\Facades\File;

it('has a Spanish translation for every sentence the application translates', function (): void {
    // Given
    $translations = json_decode(File::get(lang_path('poetainos/es.json')), true, flags: JSON_THROW_ON_ERROR);

    // When
    $untranslated = collect(File::allFiles(app_path()))
        ->flatMap(function (SplFileInfo $file): array {
            preg_match_all("/__\\('((?:[^'\\\\]|\\\\.)*)'/", File::get($file->getPathname()), $matches);

            return array_map(stripslashes(...), $matches[1]);
        })
        ->unique()
        ->reject(fn (string $sentence): bool => array_key_exists($sentence, $translations))
        ->values()
        ->all();

    // Then
    expect($untranslated)->toBe([]);
});
