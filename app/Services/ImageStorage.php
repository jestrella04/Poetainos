<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;
use Spatie\ImageOptimizer\OptimizerChain;

/**
 * Stores user images (avatars, covers) on the local disk, cropped to a fixed
 * size and optimized. Paths stay relative to the disk; the web serves each
 * folder through its symlink under public/storage.
 */
class ImageStorage
{
    private const DISK = 'local';

    private const REMOTE_MAX_BYTES = 2 * 1024 * 1024;

    private const REMOTE_TIMEOUT_SECONDS = 5;

    /**
     * Image types accepted from a remote URL, mapped to their file extension.
     *
     * @var array<int, string>
     */
    private const REMOTE_EXTENSIONS = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];

    public function __construct(private OptimizerChain $optimizer) {}

    /**
     * Persist an uploaded image cropped to the given size, returning its disk path.
     */
    public function storeUpload(UploadedFile $file, string $directory, int $width, int $height): string
    {
        $path = $file->store($directory, self::DISK);

        if ($path === false) {
            throw new RuntimeException('The uploaded image could not be stored.');
        }

        return $this->crop($path, $width, $height);
    }

    /**
     * Persist raw image bytes under a random name, cropped to the given size,
     * returning its disk path.
     */
    public function storeContents(string $contents, string $extension, string $directory, int $width, int $height): string
    {
        $path = $directory.'/'.bin2hex(random_bytes(20)).'.'.$extension;

        Storage::disk(self::DISK)->put($path, $contents);

        return $this->crop($path, $width, $height);
    }

    /**
     * Download an image and persist it cropped to the given size, returning
     * its disk path. Anything that isn't a small jpg, png or webp image is
     * ignored, as is a host that can't be reached: both return null.
     */
    public function storeRemote(string $url, string $directory, int $width, int $height): ?string
    {
        try {
            $response = Http::timeout(self::REMOTE_TIMEOUT_SECONDS)->get($url);
        } catch (ConnectionException) {
            return null;
        }

        $contents = $response->body();
        $imageInfo = $response->successful() && strlen($contents) <= self::REMOTE_MAX_BYTES
            ? getimagesizefromstring($contents)
            : false;

        if ($imageInfo === false || isset(self::REMOTE_EXTENSIONS[$imageInfo[2]]) === false) {
            return null;
        }

        return $this->storeContents($contents, self::REMOTE_EXTENSIONS[$imageInfo[2]], $directory, $width, $height);
    }

    /**
     * Remove a previously stored image. Missing or empty paths are ignored.
     */
    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
    }

    private function crop(string $path, int $width, int $height): string
    {
        $absolutePath = Storage::disk(self::DISK)->path($path);

        Image::decodePath($absolutePath)->cover($width, $height)->save();
        $this->optimizer->optimize($absolutePath);

        return $path;
    }
}
