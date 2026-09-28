<?php

namespace App\Notifications;

/**
 * What a user notification says, delivered the same way by mail and web push.
 */
final readonly class NotificationContent
{
    public function __construct(
        public string $title,
        public string $greeting,
        public string $body,
        public string $footer,
        public string $url,
        public string $action,
        public string $icon,
        public string $tag,
    ) {}
}
