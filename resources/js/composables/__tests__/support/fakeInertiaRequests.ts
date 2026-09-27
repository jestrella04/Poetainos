import { reactive } from 'vue'

/**
 * Stand-ins for Inertia's useHttp()/useForm() in unit tests: each request
 * records its method and URL and plays the next queued outcome through the
 * same callbacks (and promise behavior) as the real helpers.
 */

type Outcome =
  { data: unknown } | { errors: Record<string, string> } | { failure: 'http' | 'network' }

interface RequestOptions {
  onSuccess?: (data: never) => void
  onError?: (errors: Record<string, string>) => void
  onHttpException?: (response: { status: number }) => unknown
  onNetworkError?: (error: Error) => unknown
  onFinish?: () => void
}

export const sentRequests: Array<{ method: string; url: string; data: Record<string, unknown> }> =
  []
const queuedOutcomes: Outcome[] = []
let pendingHold: Promise<void> | null = null

export function queueOutcome(outcome: Outcome): void {
  queuedOutcomes.push(outcome)
}

/**
 * Keep the next request in flight until the returned function is called.
 */
export function holdNextRequest(): () => void {
  let release: () => void = () => undefined
  pendingHold = new Promise<void>((resolve) => {
    release = resolve
  })

  return () => {
    release()
  }
}

export function resetFakeRequests(): void {
  sentRequests.length = 0
  queuedOutcomes.length = 0
  pendingHold = null
}

function fakeRequestHelper(initialData: Record<string, unknown> = {}) {
  const defaults = { ...initialData }
  let transformData = (data: Record<string, unknown>): Record<string, unknown> => data

  const noErrors: Record<string, string> = {}
  const state = reactive({
    ...initialData,
    processing: false,
    errors: noErrors,
    transform(callback: (data: Record<string, unknown>) => Record<string, unknown>) {
      transformData = callback
      return state
    },
    reset(...fields: string[]) {
      const keys = fields.length > 0 ? fields : Object.keys(defaults)
      for (const key of keys) {
        ;(state as Record<string, unknown>)[key] = defaults[key]
      }
    },
    clearErrors() {
      state.errors = {}
    },
    cancel() {
      // Requests settle through their queued outcome, cancelled or not
    },
    get: (url: string, options: RequestOptions = {}) => send('get', url, options),
    post: (url: string, options: RequestOptions = {}) => send('post', url, options),
    put: (url: string, options: RequestOptions = {}) => send('put', url, options),
    delete: (url: string, options: RequestOptions = {}) => send('delete', url, options)
  })

  async function send(method: string, url: string, options: RequestOptions): Promise<unknown> {
    const data = Object.fromEntries(
      Object.keys(defaults).map((key) => [key, (state as Record<string, unknown>)[key]])
    )
    sentRequests.push({ method, url, data: transformData(data) })
    state.processing = true

    // A real request settles later, never in the same tick it was sent
    const hold = pendingHold ?? Promise.resolve()
    pendingHold = null
    await hold

    const outcome = queuedOutcomes.shift() ?? { data: {} }

    try {
      if ('data' in outcome) {
        options.onSuccess?.(outcome.data as never)
        return outcome.data
      }

      if ('errors' in outcome) {
        state.errors = outcome.errors
        options.onError?.(outcome.errors)
        return undefined
      }

      if (outcome.failure === 'http') {
        options.onHttpException?.({ status: 500 })
      } else {
        options.onNetworkError?.(new Error('network'))
      }

      throw new Error(outcome.failure)
    } finally {
      state.processing = false
      options.onFinish?.()
    }
  }

  return state
}

export function fakeUseHttp(initialData: Record<string, unknown> = {}) {
  return fakeRequestHelper(initialData)
}

/**
 * Router visits report failures through callbacks without rejecting.
 */
export function fakeUseForm(initialData: Record<string, unknown> = {}) {
  const helper = fakeRequestHelper(initialData)

  for (const method of ['get', 'post', 'put', 'delete'] as const) {
    const send = helper[method]
    helper[method] = (url: string, options: RequestOptions = {}) =>
      send(url, options).catch(() => undefined)
  }

  return helper
}
