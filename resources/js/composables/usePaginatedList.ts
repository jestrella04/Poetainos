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
  const { isBlank } = useTypeGuards()
  const request = useHttp<Record<string, never>, Paginated<T>>()
  const items = ref([]) as Ref<T[]>
  const nextPageUrl = ref('')
  const isFetched = ref(false)

  function update(data: T[], followingPageUrl: string | null): void {
    items.value.push(...data)
    nextPageUrl.value = followingPageUrl ?? ''
    isFetched.value = true
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
   * loaded. A newer call cancels an older one still in flight, which then
   * resolves as loaded without touching the list: it was superseded, not failed.
   */
  async function loadFirstPage(url: string): Promise<boolean> {
    const load = ++latestFirstPageLoad
    request.cancel()
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
    if (isBlank(nextPageUrl.value)) {
      done('empty')
      return
    }

    const page = await fetchPage(nextPageUrl.value)

    if (page === null) {
      done('error')
      return
    }

    update(page.data, page.next_page_url)
    done('ok')
  }

  return { items, nextPageUrl, isFetched, update, loadFirstPage, loadMore }
}
