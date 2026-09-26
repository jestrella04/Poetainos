import { onBeforeUnmount, onMounted } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

interface NotificationEventPayload {
  notifications: { unread: number }
}

function optionalPort(port: string | undefined): number | undefined {
  return port === undefined || port === '' ? undefined : Number(port)
}

function createEcho(): Echo<'reverb'> {
  const port = optionalPort(import.meta.env.VITE_REVERB_PORT)

  return new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: port,
    wssPort: port,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    // PusherConnector connects synchronously during Echo's constructor, so
    // Pusher must be supplied here rather than assigned on the instance
    // afterwards (the connection attempt would already have failed).
    Pusher
  })
}

/**
 * While the calling component is mounted, listens on the user's private
 * channel for the server's unread-notification counts, and closes the
 * connection when the component goes away.
 */
export function useNotificationsChannel(
  userId: () => number | null,
  onUnreadCount: (unread: number) => void
): void {
  let echo: Echo<'reverb'> | null = null

  onMounted(() => {
    const id = userId()

    if (id === null) {
      return
    }

    echo = createEcho()
    echo
      .private(`notifications.${id}`)
      .listen('NotificationEvent', (payload: NotificationEventPayload) => {
        onUnreadCount(payload.notifications.unread)
      })
  })

  onBeforeUnmount(() => {
    echo?.disconnect()
    echo = null
  })
}
