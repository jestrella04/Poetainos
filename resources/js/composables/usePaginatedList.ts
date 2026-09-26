import { ref, type Ref } from 'vue'
import { useHttp } from '@inertiajs/vue3'
import { useTypeGuards } from '@/composables/useTypeGuards'
import type { Paginated } from '@/types/models'

type InfiniteScrollStatus = 'ok' | 'empty' | 'loading' | 'error'

/**
 * A list fed page by page from a Laravel paginator: holds the items loaded so
 * far and the next page's URL, and appends each following page on demand.
 */
export function usePaginatedList<T>() {
  const { strNullOrEmpty } = useTypeGuards()
  const request = useHttp<Record<string, never>, Paginated<T>>()
  const items = ref([]) as Ref<T[]>
  const next = ref('')
  const fetched = ref(false)

  function update(data: T[], nextPageUrl: string | null): void {
    items.value.push(...data)
    next.value = nextPageUrl ?? ''
    fetched.value = true
  }

  /**
   * One page of the list, or null when it couldn't be loaded.
   */
  async function fetchPage(url: string): Promise<Paginated<T> | null> {
    try {
      return await request.get(url)
    } catch {
      return null
    }
  }

  let latestFirstPageLoad = 0

  /**
   * Replace the list with the first page at the given URL. Resolves whether it
   * loaded. A newer call cancels an older one still in flight; the older one
   * then resolves as loaded without touching the list, since it was superseded
   * rather than failed.
   */
  async function loadFirstPage(url: string): Promise<boolean> {
    const load = ++latestFirstPageLoad
    const page = await fetchPage(url)

    if (load !== latestFirstPageLoad) {
      return true
    }

    if (page === null) {
      return false
    }

    items.value = []
    update(page.data, page.next_page_url)

    return true
  }

  async function loadMore({
    done
  }: {
    done: (status: InfiniteScrollStatus) => void
  }): Promise<void> {
    if (strNullOrEmpty(next.value)) {
      done('empty')
      return
    }

    const page = await fetchPage(next.value)

    if (page === null) {
      done('error')
      return
    }

    update(page.data, page.next_page_url)
    done('ok')
  }

  return { items, next, fetched, update, loadFirstPage, loadMore }
}
