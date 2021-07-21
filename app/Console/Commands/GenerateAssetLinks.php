<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAssetLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:assetlinks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate assetlinks.json file (a digital file that proves ownership of a PWA)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = public_path('.well-known/assetlinks.json');
        $assetLinks = [
            0 => [
                'relation' => [
                    0 => 'delegate_permission/common.handle_all_urls',
                ],
                'target' => [
                    'namespace' => env('ANDROID_ASSETLINKS_NAMESPACE'),
                    'package_name' => env('ANDROID_ASSETLINKS_PACKAGE_NAME'),
                    'sha256_cert_fingerprints' => [
                        0 => env('ANDROID_ASSETLINKS_FINGERPRINT'),
                    ]
                ]
            ]
        ];

        if ($this->write($path, json_encode($assetLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            $this->info('The command was completed successfully!');
        } else {
            $this->error('Something went wrong!');
        }
    }

    /**
     * Write the file and create directories if they don't exist.
     *
     * @return int
     */
    private function write($path, $contents, $flags = 0)
    {
        $parts = explode('/', $path);
        array_pop($parts);
        $dir = implode('/', $parts);

        if (!is_dir($dir))
            mkdir($dir, 0775, true);

        return file_put_contents($path, $contents, $flags);
    }
}
