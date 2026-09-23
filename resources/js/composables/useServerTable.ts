import { onMounted, ref, type Ref } from 'vue'
import axios from 'axios'
import type { Paginated } from '@/types/models'

interface ServerTableEvent {
  page: number
}

/**
 * Shared load-state for the admin `v-data-table-server` listings (users,
 * categories, tags, pages, writings, complaints). Centralizes error
 * handling so a failed request always resets `isLoading`, which the
 * individual admin components previously didn't do.
 */
export function useServerTable<T>(routeName: string, initialTotal: number) {
  const items: Ref<T[]> = ref([])
  const totalItems = ref(initialTotal)
  const isLoading = ref(true)

  async function loadItems(event: ServerTableEvent): Promise<void> {
    await axios
      .get<Paginated<T>>(route(routeName, { page: event.page }))
      .then((response) => {
        items.value = response.data.data
      })
      .catch((error: unknown) => {
        console.error(error)
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  onMounted(() => {
    void loadItems({ page: 1 })
  })

  return { items, totalItems, isLoading, loadItems }
}
