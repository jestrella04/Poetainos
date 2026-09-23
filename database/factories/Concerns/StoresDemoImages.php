<?php

namespace Database\Factories\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StoresDemoImages
{
    /**
     * Copy a bundled image into the given upload directory under a random
     * 40-character name, mirroring UploadedFile::hashName(), so seeded images
     * look like real uploads and never collide. Returns the disk path.
     *
     * The source is read from the repository's public folder rather than
     * public_path(), which tests may point elsewhere.
     *
     * Callers wrap this in a lazy attribute closure so the copy is skipped
     * whenever the attribute is overridden.
     */
    private function storeDemoImage(string $publicImagePath, string $directory): string
    {
        $path = $directory.'/'.Str::random(40).'.'.pathinfo($publicImagePath, PATHINFO_EXTENSION);

        Storage::disk('local')->put($path, File::get(base_path('public/'.$publicImagePath)));

        return $path;
    }
}
