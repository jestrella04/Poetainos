import { router } from '@inertiajs/vue3'
import type { Method, RequestPayload, VisitOptions } from '@inertiajs/core'

/**
 * Shared Inertia `router.visit()` wrapper used by components that expose an
 * optional `href`/`inertia`/`method`/`data` set (PoButton, PoChip, PoListItem, PoTab, PoLink).
 */
export function useInertiaVisit(props: {
  href?: string
  inertia?: boolean
  method?: Method
  data?: RequestPayload
}) {
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

  function handleClick(event: MouseEvent | KeyboardEvent): void {
    if (props.inertia !== true) {
      return
    }

    event.preventDefault()
    visit()
  }

  return { visit, handleClick }
}
