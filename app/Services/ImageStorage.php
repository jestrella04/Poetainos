<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
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

        Image::read($absolutePath)->cover($width, $height)->save();
        app(OptimizerChain::class)->optimize($absolutePath);

        return $path;
    }
}
