<?php

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->publicPath = sys_get_temp_dir().'/assetlinks-test-'.uniqid();
    File::ensureDirectoryExists($this->publicPath);
    app()->usePublicPath($this->publicPath);
});

afterEach(function (): void {
    File::deleteDirectory($this->publicPath);
});

describe('the generate:assetlinks command', function (): void {
    it('writes the configured Android app identity to .well-known/assetlinks.json', function (): void {
        // Given
        config(['services.google.android.assetlinks' => [
            'namespace' => 'android_app',
            'package_name' => 'com.example.poetainos',
            'fingerprint' => 'AA:BB:CC',
        ]]);

        // When
        $this->artisan('generate:assetlinks')->assertSuccessful();

        // Then
        $assetLinks = json_decode((string) file_get_contents($this->publicPath.'/.well-known/assetlinks.json'), true);
        expect($assetLinks)->toBe([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => 'com.example.poetainos',
                'sha256_cert_fingerprints' => ['AA:BB:CC'],
            ],
        ]]);
    });
});
