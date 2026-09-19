import { ref, watch } from 'vue'
import axios from 'axios'

export interface ReactionToggleSource {
  count: number
  isActive: boolean
  /** Route to POST the toggle to; the endpoint returns the updated count. */
  postUrl: string
  /** Whether this viewer is allowed to react (e.g. not the content's own author). */
  canReact: boolean
}

export interface ReactionToggleOptions {
  isAuthenticated: () => boolean
  /** Called when a logged-out viewer tries to react. */
  onUnauthenticated: () => void
}

/**
 * Reactive like/shelf toggle: keeps a local count and active state seeded from
 * the source, and replaces them with the server's answer after each toggle.
 */
export function useReactionToggle(source: ReactionToggleSource, options: ReactionToggleOptions) {
  const count = ref(source.count)
  const isActive = ref(source.isActive)
  const isSubmitting = ref(false)

  watch(
    () => source.count,
    (value) => {
      count.value = value
    }
  )

  watch(
    () => source.isActive,
    (value) => {
      isActive.value = value
    }
  )

  async function toggle(): Promise<void> {
    if (options.isAuthenticated() === false) {
      options.onUnauthenticated()
      return
    }

    if (source.canReact === false || isSubmitting.value === true) {
      return
    }

    isSubmitting.value = true

    try {
      const response = await axios.post<{ count: number; method: 'store' | 'destroy' }>(
        source.postUrl
      )

      count.value = response.data.count
      isActive.value = response.data.method === 'store'
    } catch {
      // Keep the previous state when the request fails.
    } finally {
      isSubmitting.value = false
    }
  }

  return { count, isActive, isSubmitting, toggle }
}
