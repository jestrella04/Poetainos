import { onBeforeUnmount, onMounted } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// What PoetainosNotification::toBroadcast() sends, next to the `id` and `type` Laravel adds
interface UnreadCountNotification {
  unread: number
}

function optionalPort(port: string | undefined): number | undefined {
  return port === undefined || port === '' ? undefined : Number(port)
}

function createEcho(): Echo<'reverb'> {
  const port = optionalPort(import.meta.env.VITE_REVERB_PORT)
  const isSecure = (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https'

  return new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: port,
    wssPort: port,
    forceTLS: isSecure,
    // Only the scheme's own transport: Pusher otherwise retries a failed ws
    // connection over wss (or back), which the server doesn't serve
    enabledTransports: [isSecure ? 'wss' : 'ws'],
    // PusherConnector connects synchronously during Echo's constructor, so
    // Pusher must be supplied here rather than assigned on the instance
    // afterwards (the connection attempt would already have failed).
    Pusher
  })
}

/**
 * While the calling component is mounted, listens on the user's private
 * notification channel for the server's unread-notification counts, and closes the
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
    echo.private(`App.Models.User.${id}`).notification((notification: UnreadCountNotification) => {
      onUnreadCount(notification.unread)
    })
  })

  onBeforeUnmount(() => {
    echo?.disconnect()
    echo = null
  })
}
