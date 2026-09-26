import { computed, onMounted, ref, watch, type Ref } from 'vue'
import { useHttp } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import { useRequestFailure } from '@/composables/useRequestFailure'

export interface LogEntry {
  level: string | null
  environment: string | null
  date: string | null
  message: string
  details: string
}

interface LogPage {
  entries: LogEntry[]
  before: number | null
}

const SEARCH_DELAY_MS = 300

/**
 * The entries of one log file for the admin log viewer, newest first,
 * filtered by minimum level and search text. Each page ends where the
 * next, older one starts (`before`), so older entries load on demand.
 */
export function useLogEntries(initialFile: string) {
  const file = ref(initialFile)
  const level: Ref<string | null> = ref(null)
  const search = ref('')
  const entries: Ref<LogEntry[]> = ref([])
  const before: Ref<number | null> = ref(null)
  const request = useHttp<Record<string, never>, LogPage>()
  const isLoading = computed(() => request.processing)
  const hasOlder = computed(() => before.value !== null)
  const { onHttpException, onNetworkError, whenSettled } = useRequestFailure()

  // A response to a request made before the filters changed must not land in the new list
  let latestRequest = 0

  async function loadPage(cursor: number | null): Promise<void> {
    const requestNumber = ++latestRequest
    const params = {
      file: file.value,
      ...(cursor === null ? {} : { before: cursor }),
      ...(level.value === null ? {} : { level: level.value }),
      ...(search.value === '' ? {} : { search: search.value })
    }

    await whenSettled(
      request.get(route('admin.logs.entries', params), {
        onHttpException,
        onNetworkError,
        onSuccess: (page) => {
          if (requestNumber === latestRequest) {
            entries.value = cursor === null ? page.entries : [...entries.value, ...page.entries]
            before.value = page.before
          }
        }
      })
    )
  }

  async function reload(): Promise<void> {
    await loadPage(null)
  }

  async function loadOlder(): Promise<void> {
    if (before.value !== null) {
      await loadPage(before.value)
    }
  }

  watch([file, level], () => {
    void reload()
  })
  watch(
    search,
    debounce(() => {
      void reload()
    }, SEARCH_DELAY_MS)
  )

  onMounted(() => {
    void reload()
  })

  return { file, level, search, entries, isLoading, hasOlder, reload, loadOlder }
}
