import { router } from '@inertiajs/vue3'
import type { Method, RequestPayload, VisitOptions } from '@inertiajs/core'

/**
 * Shared Inertia `router.visit()` wrapper used by components that expose an
 * optional `href`/`method`/`data` triplet (PoButton, PoChip, PoListItem, PoTab, PoLink).
 */
export function useInertiaVisit(props: { href?: string; method?: Method; data?: RequestPayload }) {
  function visit(): void {
    if (props.href === undefined || props.href === '') {
      return
    }

    const visitOptions: VisitOptions = { method: props.method ?? 'get' }
    if (props.data !== undefined) {
      visitOptions.data = props.data
    }

    router.visit(props.href, visitOptions)
  }

  return { visit }
}
