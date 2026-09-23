<?php

beforeEach(function (): void {
    useTemporaryPublicPath();
});

afterEach(function (): void {
    deleteTemporaryPublicPath();
});

describe('the generate:assetlinks command', function (): void {
    it('writes the configured Android app identity to .well-known/assetlinks.json', function (): void {
        // Given
        $packageName = 'com.'.fake()->domainWord().'.'.fake()->domainWord();
        $fingerprint = implode(':', str_split(strtoupper(fake()->sha256()), 2));
        config(['services.google.android.assetlinks' => [
            'namespace' => 'android_app',
            'package_name' => $packageName,
            'fingerprint' => $fingerprint,
        ]]);

        // When
        pendingArtisan('generate:assetlinks')->assertSuccessful();

        // Then
        $assetLinks = json_decode((string) file_get_contents(public_path('.well-known/assetlinks.json')), true);
        expect($assetLinks)->toBe([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => $packageName,
                'sha256_cert_fingerprints' => [$fingerprint],
            ],
        ]]);
    });
});
