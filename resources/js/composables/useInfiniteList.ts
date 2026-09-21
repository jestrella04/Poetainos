import { onMounted, ref, type Ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { useTypeGuards } from '@/composables/useTypeGuards'
import type { InertiaPageProps } from '@/types/inertia'
import type { Paginated } from '@/types/models'

export type InfiniteScrollStatus = 'ok' | 'empty' | 'loading' | 'error'

/**
 * Infinite-scroll list behavior shared by the paginated pages (users,
 * writings, notifications): loads the first page on mount through a partial
 * reload of `reloadPropKey`, and fetches the following pages via axios.
 */
export function useInfiniteList<T>(reloadPropKey: string) {
  const { strNullOrEmpty } = useTypeGuards()
  const items = ref([]) as Ref<T[]>
  const next = ref('')
  const fetched = ref(false)

  function update(data: T[], nextPageUrl: string | null): void {
    items.value.push(...data)
    next.value = nextPageUrl ?? ''
    fetched.value = true
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

    await axios
      .get<Paginated<T>>(next.value)
      .then((response) => {
        update(response.data.data, response.data.next_page_url)
        done('ok')
      })
      .catch(() => {
        done('error')
      })
  }

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
