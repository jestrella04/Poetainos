// Import Workbox service worker script
importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.1.5/workbox-sw.js');

const CACHE = "pwabuilder-page";

// Offline fallback page i.e.: const offlineFallbackPage = "offline.html";
const offlineFallbackPage = "offline";

self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

self.addEventListener('install', async (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.add(offlineFallbackPage))
    );
});

if (workbox.navigationPreload.isSupported()) {
    workbox.navigationPreload.enable();
}

self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const preloadResp = await event.preloadResponse;

                if (preloadResp) {
                    return preloadResp;
                }

                const networkResp = await fetch(event.request);
                return networkResp;
            } catch (error) {
                const cache = await caches.open(CACHE);
                const cachedResp = await cache.match(offlineFallbackPage);
                return cachedResp;
            }
        })());
    }
});


// Webpush notifications manager
(() => {
    'use strict'

    const WebPush = {
        init() {
            self.addEventListener('push', this.notificationPush.bind(this))
            self.addEventListener('notificationclick', this.notificationClick.bind(this))
            self.addEventListener('notificationclose', this.notificationClose.bind(this))
        },

        /**
         * Handle notification push event.
         *
         * https://developer.mozilla.org/en-US/docs/Web/Events/push
         *
         * @param {NotificationEvent} event
         */
        notificationPush(event) {
            if (!(self.Notification && self.Notification.permission === 'granted')) {
                return
            }

            // https://developer.mozilla.org/en-US/docs/Web/API/PushMessageData
            if (event.data) {
                event.waitUntil(
                    this.sendNotification(event.data.json())
                )
            }
        },

        /**
         * Handle notification click event.
         *
         * https://developer.mozilla.org/en-US/docs/Web/Events/notificationclick
         *
         * @param {NotificationEvent} event
         */
        notificationClick(event) {
            // console.log(event.notification)
            self.clients.openWindow(event.action)
        },

        /**
         * Handle notification close event (Chrome 50+, Firefox 55+).
         *
         * https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerGlobalScope/onnotificationclose
         *
         * @param {NotificationEvent} event
         */
        notificationClose(event) {
            self.registration.pushManager.getSubscription().then(subscription => {
                if (subscription) {
                    this.dismissNotification(event, subscription)
                }
            })
        },

        /**
         * Send notification to the user.
         *
         * https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerRegistration/showNotification
         *
         * @param {PushMessageData|Object} data
         */
        sendNotification(data) {
            return self.registration.showNotification(data.title, data)
        },

        /**
         * Send request to server to dismiss a notification.
         *
         * @param  {NotificationEvent} event
         * @param  {String} subscription.endpoint
         * @return {Response}
         */
        dismissNotification({ notification }, { endpoint }) {
            if (!notification.data || !notification.data.id) {
                return
            }

            const data = new FormData()
            data.append('endpoint', endpoint)

            // Send a request to the server to mark the notification as read.
            fetch(`/notifications/read/${notification.data.id}`, {
                method: 'POST',
                body: data
            })
        }
    }

    WebPush.init()
})();
