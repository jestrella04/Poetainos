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

        if (cache()->add($itemKey.':'.$this->viewerKey(), true, $cooldownEndsAt) === false) {
            return;
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
     * Identify the viewer by account, then visitor cookie, then IP and user agent,
     * handing cookieless guests a visitor cookie for their next visits.
     */
    private function viewerKey(): string
    {
        $userId = auth()->guard()->id();

        if ($userId !== null) {
            return 'user:'.$userId;
        }

        $visitorId = request()->cookie(self::VISITOR_COOKIE);

        if (is_string($visitorId) === true && Str::isUuid($visitorId) === true) {
            return 'visitor:'.$visitorId;
        }

        cookie()->queue(self::VISITOR_COOKIE, (string) Str::uuid(), self::VISITOR_COOKIE_MINUTES);

        return 'guest:'.hash('sha256', request()->ip().'|'.request()->userAgent());
    }
}
