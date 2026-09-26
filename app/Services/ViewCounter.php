<?php

namespace App\Services;

use App\Models\User;
use App\Models\Writing;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Counts views of writings and profiles once per viewer per cooldown window,
 * so a genuine reread on another day counts while reloads don't inflate it.
 * Guests are told apart by an encrypted visitor cookie, keeping people behind
 * a shared IP distinct. Cookieless clients fall back to their IP and user
 * agent, and a per-IP cap stops scripts replaying many harvested cookies.
 */
class ViewCounter
{
    public const COOLDOWN_HOURS = 1;

    public const MAX_VIEWS_PER_IP = 30;

    public const VISITOR_COOKIE = 'visitor_id';

    private const VISITOR_COOKIE_MINUTES = 60 * 24 * 365;

    private const CRAWLER_PATTERN = '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp/i';

    /**
     * Count a view of the item unless this viewer already did within the cooldown window.
     */
    public function count(User|Writing $viewed): void
    {
        if ($this->isOwnedByViewer($viewed) === true || $this->isCrawler() === true) {
            return;
        }

        $itemKey = 'views:'.$viewed->getTable().':'.$viewed->getKey();
        $ipKey = $itemKey.':ip:'.request()->ip();

        if (RateLimiter::tooManyAttempts($ipKey, self::MAX_VIEWS_PER_IP) === true) {
            return;
        }

        $cooldownEndsAt = Carbon::now()->addHours(self::COOLDOWN_HOURS);

        foreach ($this->viewerKeys() as $viewerKey) {
            if (cache()->add($itemKey.':'.$viewerKey, true, $cooldownEndsAt) === false) {
                return;
            }
        }

        $viewed->incrementViews();
        RateLimiter::hit($ipKey, self::COOLDOWN_HOURS * 3600);
    }

    private function isOwnedByViewer(User|Writing $viewed): bool
    {
        $ownerId = $viewed instanceof Writing ? $viewed->user_id : $viewed->id;

        return $ownerId === auth()->guard()->id();
    }

    private function isCrawler(): bool
    {
        return preg_match(self::CRAWLER_PATTERN, (string) request()->userAgent()) === 1;
    }

    /**
     * The keys that identify the viewer, all of which must be new for the view
     * to count: the account, else the visitor cookie. A guest without a cookie
     * is handed one and keyed by it right away, so their next visit (which
     * carries it) isn't counted again; their IP and user agent are keyed too,
     * so a client that never sends the cookie back is only counted once.
     *
     * @return array<int, string>
     */
    private function viewerKeys(): array
    {
        $userId = auth()->guard()->id();

        if ($userId !== null) {
            return ['user:'.$userId];
        }

        $visitorId = request()->cookie(self::VISITOR_COOKIE);

        if (is_string($visitorId) === true && Str::isUuid($visitorId) === true) {
            return ['visitor:'.$visitorId];
        }

        $visitorId = (string) Str::uuid();
        cookie()->queue(self::VISITOR_COOKIE, $visitorId, self::VISITOR_COOKIE_MINUTES);

        return [
            'visitor:'.$visitorId,
            'guest:'.hash('sha256', request()->ip().'|'.request()->userAgent()),
        ];
    }
}
