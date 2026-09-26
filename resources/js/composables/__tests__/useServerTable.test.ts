import { describe, expect, it, vi, beforeEach } from 'vitest'
import { useServerTable } from '../useServerTable'
import { queueOutcome, resetFakeRequests, sentRequests } from './support/fakeInertiaRequests'

vi.mock('@inertiajs/vue3', async () => {
  const { fakeUseHttp } = await import('./support/fakeInertiaRequests')

  return { useHttp: fakeUseHttp, router: { flash: vi.fn() } }
})

beforeEach(() => {
  resetFakeRequests()
  vi.stubGlobal(
    'route',
    vi.fn((name: string, params: { page: number }) => `${name}?page=${params.page}`)
  )
})

describe('useServerTable', () => {
  describe('loadItems', () => {
    it('loads the requested page of items and stops loading', async () => {
      // Given
      queueOutcome({ data: { data: [{ id: 1 }], next_page_url: null } })
      const { items, isLoading, loadItems } = useServerTable<{ id: number }>('admin.tags', 0)

      // When
      await loadItems({ page: 2 })

      // Then
      expect(sentRequests).toEqual([expect.objectContaining({ url: 'admin.tags?page=2' })])
      expect(items.value).toEqual([{ id: 1 }])
      expect(isLoading.value).toBe(false)
    })

    it('stops loading instead of spinning forever when the request fails', async () => {
      // Given
      queueOutcome({ failure: 'network' })
      const { items, isLoading, loadItems } = useServerTable<{ id: number }>('admin.tags', 0)

      // When
      await loadItems({ page: 1 })

      // Then
      expect(isLoading.value).toBe(false)
      expect(items.value).toEqual([])
    })
  })
})
