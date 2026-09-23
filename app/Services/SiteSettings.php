<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Database\QueryException;

/**
 * Loads the `site` row of the settings table into the `poetainos` config
 * key that getSiteConfig() reads. Cached so that no request, artisan command
 * or queue worker needs to hit the settings table more than once.
 */
class SiteSettings
{
    public const CONFIG_KEY = 'poetainos';

    private const CACHE_KEY = 'site.settings';

    /**
     * Whether the settings are available, loading them into config if needed.
     */
    public function load(): bool
    {
        if (config(self::CONFIG_KEY) !== null) {
            return true;
        }

        try {
            $data = cache()->rememberForever(
                self::CACHE_KEY,
                fn (): ?array => Setting::where('name', 'site')->value('data'),
            );
        } catch (QueryException) {
            // Expected before the installer runs: the `settings` table may not exist yet.
            return false;
        }

        if ($data === null) {
            return false;
        }

        config([self::CONFIG_KEY => $data]);

        return true;
    }

    /**
     * Drop the cached copy and reload it, after the settings row changed.
     */
    public function refresh(): bool
    {
        cache()->forget(self::CACHE_KEY);
        config([self::CONFIG_KEY => null]);

        return $this->load();
    }
}
