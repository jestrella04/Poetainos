import { http } from '@inertiajs/vue3'
import type { App } from 'vue'
import { pushKey } from '../composables/keys'

interface SubscriptionPayload {
  endpoint: string
  publicKey: string | null
  authToken: string | null
  contentEncoding: string
}

function reportFailure(message: string, error: unknown): void {
  if (import.meta.env.DEV) {
    console.warn(message, error)
  }
}

function toBase64(key: ArrayBuffer | null): string | null {
  return key !== null ? btoa(String.fromCharCode(...new Uint8Array(key))) : null
}

export class Push {
  /**
   * Subscribe for push notifications.
   */
  async subscribe(): Promise<void> {
    const registration = await navigator.serviceWorker.ready
    const options: PushSubscriptionOptionsInit = { userVisibleOnly: true }
    const vapidPublicKey = import.meta.env.VITE_VAPID_PUBLIC_KEY

    if (vapidPublicKey !== undefined && vapidPublicKey !== '') {
      options.applicationServerKey = this.urlBase64ToUint8Array(vapidPublicKey)
    }

    try {
      const subscription = await registration.pushManager.subscribe(options)
      await this.updateSubscription(subscription)
    } catch (error: unknown) {
      // Declining the browser's permission prompt lands here too
      reportFailure('Unable to subscribe to push notifications.', error)
    }
  }

  /**
   * Whether the browser currently holds a push subscription.
   */
  async isSubscribed(): Promise<boolean> {
    if (typeof navigator === 'undefined' || !('serviceWorker' in navigator)) {
      return false
    }

    try {
      const registration = await navigator.serviceWorker.ready
      const subscription = await registration.pushManager.getSubscription()
      return subscription !== null
    } catch (error: unknown) {
      reportFailure('Unable to check the push subscription.', error)
      return false
    }
  }

  /**
   * Unsubscribe from push notifications.
   */
  async unsubscribe(): Promise<void> {
    try {
      const registration = await navigator.serviceWorker.ready
      const subscription = await registration.pushManager.getSubscription()

      if (subscription === null) {
        return
      }

      await subscription.unsubscribe()
      await this.deleteSubscription(subscription)
    } catch (error: unknown) {
      reportFailure('Unable to unsubscribe from push notifications.', error)
    }
  }

  /**
   * Send a request to the server to update user's subscription.
   */
  async updateSubscription(subscription: PushSubscription): Promise<void> {
    const data: SubscriptionPayload = {
      endpoint: subscription.endpoint,
      publicKey: toBase64(subscription.getKey('p256dh')),
      authToken: toBase64(subscription.getKey('auth')),
      contentEncoding: (PushManager.supportedContentEncodings ?? ['aesgcm'])[0] ?? 'aesgcm'
    }

    await http.getClient().request({ method: 'post', url: route('push.update'), data })
  }

  /**
   * Send a request to the server to delete user's subscription.
   */
  async deleteSubscription(subscription: PushSubscription): Promise<void> {
    await http.getClient().request({
      method: 'post',
      url: route('push.delete'),
      data: { endpoint: subscription.endpoint }
    })
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
