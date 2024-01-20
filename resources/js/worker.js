import { precacheAndRoute, cleanupOutdatedCaches } from 'workbox-precaching'
import * as navigationPreload from 'workbox-navigation-preload'
import { clientsClaim } from 'workbox-core'

const CACHE = 'po-cache'
const entries = self.__WB_MANIFEST
const offlineFallbackPage = 'offline'

self.skipWaiting()
clientsClaim()

cleanupOutdatedCaches()
precacheAndRoute(entries)

self.addEventListener('install', async (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.add(offlineFallbackPage)))
})

if (navigationPreload.isSupported()) {
  navigationPreload.enable()
}

self.addEventListener('fetch', (event) => {
  if (event.request.mode === 'navigate') {
    event.respondWith(
      (async () => {
        try {
          const preloadResp = await event.preloadResponse

          if (preloadResp) {
            return preloadResp
          }

          const networkResp = await fetch(event.request)
          return networkResp
        } catch (error) {
          const cache = await caches.open(CACHE)
          const cachedResp = await cache.match(offlineFallbackPage)
          return cachedResp
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

function sendNotification(data) {
  return self.registration.showNotification(data.title, data)
}

self.addEventListener('push', (event) => {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return
  }

  // https://developer.mozilla.org/en-US/docs/Web/API/PushMessageData
  if (event.data) {
    event.waitUntil(sendNotification(event.data.json()))
  }
})

self.addEventListener('notificationclick', (event) => {
  const clickedNotification = event.notification
  let action = null
  let url = null

  if (!action) {
    //Default to the first action defined
    url = clickedNotification.actions[0].action
  } else {
    url = clickedNotification.actions[action].action
  }

  const promiseChain = self.clients
    .matchAll({
      type: 'window',
      includeUncontrolled: true
    })
    .then((windowClients) => {
      let matchingClient = null

      for (let i = 0; i < windowClients.length; i++) {
        const windowClient = windowClients[i]

        if (windowClient.url === url) {
          matchingClient = windowClient
          break
        }
      }

      if (matchingClient) {
        return matchingClient.focus()
      } else {
        return self.clients.openWindow(url)
      }
    })

  event.waitUntil(promiseChain)
  clickedNotification.close()
})
