import { onMounted, ref, type Ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { useSwipe } from '@vueuse/core'
import type { UseSwipeDirection } from '@vueuse/core'
import { useTypeGuards } from '@/composables/useTypeGuards'
import type { InertiaPageProps } from '@/types/inertia'
import type { Paginated } from '@/types/models'

export type InfiniteScrollStatus = 'ok' | 'empty' | 'loading' | 'error'

/**
 * Shared infinite-scroll + swipeable-tabs behavior for the sort/filter tab
 * lists (users, writings, notifications): reloads the current tab's first
 * page on mount, paginates further pages via axios, and lets a horizontal
 * swipe move to the next/previous tab per `tabOrder`.
 */
export function usePaginatedTabList<T>(config: {
  tabOrder: string[]
  currentTab: () => string
  reloadPropKey: string
  swipeTarget?: HTMLElement
}) {
  const { strNullOrEmpty } = useTypeGuards()
  const items = ref([]) as Ref<T[]>
  const next = ref('')
  const fetched = ref(false)

  function clickTab(tabName: string): void {
    document.querySelector<HTMLElement>(`.v-tab[value="${tabName}"]`)?.click()
  }

  function goToAdjacentTab(step: 1 | -1): void {
    const currentIndex = config.tabOrder.indexOf(config.currentTab())
    const targetTab = config.tabOrder[currentIndex + step]

    if (currentIndex === -1 || targetTab === undefined) {
      return
    }

    clickTab(targetTab)
  }

  useSwipe(config.swipeTarget ?? document.body, {
    passive: true,
    onSwipe() {
      //
    },
    onSwipeEnd(_event: TouchEvent, direction: UseSwipeDirection) {
      if (direction === 'left') {
        goToAdjacentTab(1)
      } else if (direction === 'right') {
        goToAdjacentTab(-1)
      }
    }
  })

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
      only: [config.reloadPropKey],
      onSuccess: (successPage) => {
        const successProps = successPage.props as unknown as InertiaPageProps<
          Record<string, Paginated<T>>
        >
        const pageData = successProps[config.reloadPropKey]

        if (pageData !== undefined) {
          update(pageData.data, pageData.next_page_url)
        }
      }
    })
  })

  return { items, next, fetched, loadMore }
}
