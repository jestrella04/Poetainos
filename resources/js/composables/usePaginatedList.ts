import { ref, type Ref } from 'vue'
import axios from 'axios'
import { useTypeGuards } from '@/composables/useTypeGuards'
import type { Paginated } from '@/types/models'

type InfiniteScrollStatus = 'ok' | 'empty' | 'loading' | 'error'

/**
 * A list fed page by page from a Laravel paginator: holds the items loaded so
 * far and the next page's URL, and appends each following page on demand.
 */
export function usePaginatedList<T>() {
  const { strNullOrEmpty } = useTypeGuards()
  const items = ref([]) as Ref<T[]>
  const next = ref('')
  const fetched = ref(false)

  function update(data: T[], nextPageUrl: string | null): void {
    items.value.push(...data)
    next.value = nextPageUrl ?? ''
    fetched.value = true
  }

  /**
   * Replace the list with the first page at the given URL. Resolves whether it loaded.
   */
  async function loadFirstPage(url: string): Promise<boolean> {
    try {
      const response = await axios.get<Paginated<T>>(url)

      items.value = []
      update(response.data.data, response.data.next_page_url)

      return true
    } catch {
      return false
    }
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

    try {
      const response = await axios.get<Paginated<T>>(next.value)

      update(response.data.data, response.data.next_page_url)
      done('ok')
    } catch {
      done('error')
    }
  }

  return { items, next, fetched, update, loadFirstPage, loadMore }
}
