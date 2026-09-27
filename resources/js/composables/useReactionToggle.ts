import { computed, ref, watch } from 'vue'
import { useHttp } from '@inertiajs/vue3'
import { useRequestFailure } from '@/composables/useRequestFailure'

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
  const request = useHttp<Record<string, never>, { count: number; isActive: boolean }>()
  const isSubmitting = computed(() => request.processing)
  const { onHttpException, onNetworkError, whenSettled } = useRequestFailure()

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

    // A failed toggle keeps the previous state
    await whenSettled(
      request.post(source.postUrl, {
        onHttpException,
        onNetworkError,
        onSuccess: (data) => {
          count.value = data.count
          isActive.value = data.isActive
        }
      })
    )
  }

  return { count, isActive, isSubmitting, toggle }
}
