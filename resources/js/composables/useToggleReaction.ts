import axios from 'axios'
import { useAnimation } from '@/composables/useAnimation'

export interface ToggleReactionConfig {
  event: MouseEvent
  /** CSS selector for the ancestor element that carries the active-state class and icon. */
  doerSelector: string
  /** Whether this viewer is currently allowed to react (e.g. logged in and not the content's own author). */
  canReact: boolean
  /** Whether this viewer is authenticated at all — used only to decide whether to prompt a login. */
  isAuthenticated: boolean
  /** Called when a logged-out viewer tries to react. */
  onUnauthenticated: () => void
  /** Route to POST the toggle to. */
  postUrl: string
  /** Class toggled on the "doer" element to reflect the reaction's active state. */
  activeClass: string
  /** Receives the updated count returned by the endpoint. */
  onCount: (count: number) => void
}

/**
 * Shared like/shelf toggle behavior: find the ancestor element carrying the
 * reaction's state, POST the toggle, reflect the result via a class and the
 * caller's count callback, then animate the icon.
 */
export function useToggleReaction() {
  const { animate } = useAnimation()

  async function toggleReaction(config: ToggleReactionConfig): Promise<void> {
    const doer = (config.event.target as HTMLElement).closest<HTMLElement>(config.doerSelector)

    if (doer === null) {
      return
    }

    if (config.canReact === true) {
      await axios
        .post<{ count: number; method: 'store' | 'destroy' }>(config.postUrl)
        .then((response) => {
          config.onCount(response.data.count)

          if (response.data.method === 'store') {
            doer.classList.add(config.activeClass)
          } else {
            doer.classList.remove(config.activeClass)
          }
        })
        .catch(() => undefined)
        .finally(() => {
          const icon = doer.querySelector<HTMLElement>('i')

          if (icon !== null) {
            void animate(icon, 'heartBeat')
          }
        })
    } else if (config.isAuthenticated === false) {
      config.onUnauthenticated()
    }
  }

  return { toggleReaction }
}
