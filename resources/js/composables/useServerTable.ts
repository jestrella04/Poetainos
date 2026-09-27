import { computed, onMounted, ref, type Ref } from 'vue'
import { useHttp } from '@inertiajs/vue3'
import { useRequestFailure } from '@/composables/useRequestFailure'
import type { Paginated } from '@/types/models'

interface ServerTableEvent {
  page: number
}

/**
 * Shared load-state for the admin `v-data-table-server` listings (users,
 * categories, tags, pages, writings, complaints), which fetch each page of
 * rows as JSON.
 */
export function useServerTable<T>(routeName: string, initialTotal: number) {
  const items: Ref<T[]> = ref([])
  const totalItems = ref(initialTotal)
  const request = useHttp<Record<string, never>, Paginated<T>>()
  const hasLoaded = ref(false)
  const isLoading = computed(() => request.processing || hasLoaded.value === false)
  const { onHttpException, onNetworkError, whenSettled } = useRequestFailure()

  async function loadItems(event: ServerTableEvent): Promise<void> {
    await whenSettled(
      request.get(route(routeName, { page: event.page }), {
        onHttpException,
        onNetworkError,
        onSuccess: (data) => {
          items.value = data.data
        }
      })
    )

    hasLoaded.value = true
  }

  onMounted(() => {
    void loadItems({ page: 1 })
  })

  return { items, totalItems, isLoading, loadItems }
}
