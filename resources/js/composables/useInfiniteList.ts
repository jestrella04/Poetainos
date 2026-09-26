import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePaginatedList } from '@/composables/usePaginatedList'
import type { InertiaPageProps } from '@/types/inertia'
import type { Paginated } from '@/types/models'

/**
 * Infinite-scroll list behavior shared by the paginated pages (users,
 * writings, notifications): loads the first page on mount through a partial
 * reload of `reloadPropKey`, and fetches the following pages as JSON.
 */
export function useInfiniteList<T>(reloadPropKey: string) {
  const { items, next, fetched, update, loadMore } = usePaginatedList<T>()

  onMounted(() => {
    router.reload({
      only: [reloadPropKey],
      onSuccess: (successPage) => {
        const successProps = successPage.props as unknown as InertiaPageProps<
          Record<string, Paginated<T>>
        >
        const pageData = successProps[reloadPropKey]

        if (pageData !== undefined) {
          update(pageData.data, pageData.next_page_url)
        }
      }
    })
  })

  return { items, next, fetched, loadMore }
}
