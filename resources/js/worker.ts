/// <reference lib="webworker" />
import { precacheAndRoute, cleanupOutdatedCaches } from 'workbox-precaching'
import * as navigationPreload from 'workbox-navigation-preload'
import { clientsClaim } from 'workbox-core'

declare const self: ServiceWorkerGlobalScope

// TypeScript's bundled webworker lib omits the Notification actions API
// (https://developer.mozilla.org/docs/Web/API/Notification/actions), which
// this service worker relies on for push-notification action buttons.
interface NotificationAction {
  action: string
  title: string
  icon?: string
}

declare global {
  interface Notification {
    readonly actions: readonly NotificationAction[]
  }

  interface NotificationOptions {
    actions?: NotificationAction[]
  }
}

const CACHE = 'po-cache'
const entries = self.__WB_MANIFEST
const offlineFallbackPage = 'offline'

void self.skipWaiting()
clientsClaim()

cleanupOutdatedCaches()
precacheAndRoute(entries)

self.addEventListener('install', (event: ExtendableEvent) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.add(offlineFallbackPage)))
})

if (navigationPreload.isSupported()) {
  navigationPreload.enable()
}

self.addEventListener('fetch', (event: FetchEvent) => {
  if (event.request.mode === 'navigate') {
    event.respondWith(
      (async () => {
        try {
          // TS types NavigationPreloadManager's response as `any`; the spec
          // guarantees Response | undefined.
          const preloadResp = (await event.preloadResponse) as Response | undefined

          if (preloadResp) {
            return preloadResp
          }

          const networkResp = await fetch(event.request)
          return networkResp
        } catch {
          const cache = await caches.open(CACHE)
          const cachedResp = await cache.match(offlineFallbackPage)
          return cachedResp ?? Response.error()
        }
      })()
    )
  }
})

/*
 *
 * Push notifications
 *
 */

interface PushNotificationData extends NotificationOptions {
  title: string
}

function sendNotification(data: PushNotificationData) {
  return self.registration.showNotification(data.title, data)
}

self.addEventListener('push', (event: PushEvent) => {
  if (!(typeof Notification !== 'undefined' && Notification.permission === 'granted')) {
    return
  }

  // https://developer.mozilla.org/en-US/docs/Web/API/PushMessageData
  // The payload shape is a server-side contract, not something the client
  // can verify at compile time.
  if (event.data) {
    event.waitUntil(sendNotification(event.data.json() as PushNotificationData))
  }
})

self.addEventListener('notificationclick', (event: NotificationEvent) => {
  const clickedNotification = event.notification
  const actions = clickedNotification.actions
  const url = actions.length > 0 ? actions[0]?.action : undefined

  if (url === undefined) {
    return
  }

  const promiseChain = self.clients
    .matchAll({
      type: 'window',
      includeUncontrolled: true
    })
    .then((windowClients) => {
      const matchingClient = windowClients.find((windowClient) => windowClient.url === url)

      if (matchingClient) {
        return matchingClient.focus()
      } else {
        return self.clients.openWindow(url)
      }
    })

  event.waitUntil(promiseChain)
  clickedNotification.close()
})
