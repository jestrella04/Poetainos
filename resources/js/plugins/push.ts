import axios from 'axios'
import type { App } from 'vue'
import { pushKey } from '../composables/keys'

interface SubscriptionPayload {
  endpoint: string
  publicKey: string | null
  authToken: string | null
  contentEncoding: string
}

export class Push {
  /**
   * Subscribe for push notifications.
   */
  subscribe(): void {
    void navigator.serviceWorker.ready.then((registration) => {
      const options: PushSubscriptionOptionsInit = { userVisibleOnly: true }
      const vapidPublicKey = import.meta.env.VITE_VAPID_PUBLIC_KEY

      if (vapidPublicKey) {
        options.applicationServerKey = this.urlBase64ToUint8Array(vapidPublicKey)
      }

      registration.pushManager
        .subscribe(options)
        .then((subscription) => {
          this.updateSubscription(subscription)
        })
        .catch((e: unknown) => {
          if (Notification.permission === 'denied') {
            console.log('Permission for Notifications was denied')
          } else {
            console.log('Unable to subscribe to push.', e)
          }
        })
    })
  }

  /**
   * Unsubscribe from push notifications.
   */
  unsubscribe(): void {
    void navigator.serviceWorker.ready.then((registration) => {
      registration.pushManager
        .getSubscription()
        .then((subscription) => {
          if (!subscription) {
            return
          }

          subscription
            .unsubscribe()
            .then(() => {
              this.deleteSubscription(subscription)
            })
            .catch((e: unknown) => {
              console.log('Unsubscription error: ', e)
            })
        })
        .catch((e: unknown) => {
          console.log('Error thrown while unsubscribing.', e)
        })
    })
  }

  /**
   * Send a request to the server to update user's subscription.
   */
  updateSubscription(subscription: PushSubscription): void {
    const key = subscription.getKey('p256dh')
    const token = subscription.getKey('auth')
    const contentEncoding = (PushManager.supportedContentEncodings ?? ['aesgcm'])[0] ?? 'aesgcm'
    const data: SubscriptionPayload = {
      endpoint: subscription.endpoint,
      publicKey: key ? btoa(String.fromCharCode(...new Uint8Array(key))) : null,
      authToken: token ? btoa(String.fromCharCode(...new Uint8Array(token))) : null,
      contentEncoding
    }

    void axios.post('/subscriptions', data)
  }

  /**
   * Send a requst to the server to delete user's subscription.
   */
  deleteSubscription(subscription: PushSubscription): void {
    void axios.post('/subscriptions/delete', { endpoint: subscription.endpoint })
  }

  /**
   * https://github.com/Minishlink/physbook/blob/02a0d5d7ca0d5d2cc6d308a3a9b81244c63b3f14/app/Resources/public/js/app.js#L177
   */
  urlBase64ToUint8Array(base64String: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
    const rawData = window.atob(base64)
    const outputArray = new Uint8Array(new ArrayBuffer(rawData.length))

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i)
    }

    return outputArray
  }
}

export const push = {
  install: (app: App) => {
    const instance = new Push()
    app.provide(pushKey, instance)
  }
}
