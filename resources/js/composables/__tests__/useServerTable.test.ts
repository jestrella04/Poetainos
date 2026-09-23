import { describe, expect, it, vi, beforeEach } from 'vitest'
import axios from 'axios'
import { useServerTable } from '../useServerTable'

beforeEach(() => {
  vi.stubGlobal(
    'route',
    vi.fn((name: string) => name)
  )
})

describe('useServerTable', () => {
  describe('loadItems', () => {
    it('loads items and stops loading on success', async () => {
      // Given
      const get = vi.spyOn(axios, 'get').mockResolvedValueOnce({
        data: { data: [{ id: 1 }], next_page_url: null }
      })
      const { items, isLoading, loadItems } = useServerTable<{ id: number }>('admin.tags', 0)

      // When
      await loadItems({ page: 1 })

      // Then
      expect(items.value).toEqual([{ id: 1 }])
      expect(isLoading.value).toBe(false)

      get.mockRestore()
    })

    it('stops loading instead of spinning forever when the request fails', async () => {
      // Given
      const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {})
      const get = vi.spyOn(axios, 'get').mockRejectedValueOnce(new Error('network error'))
      const { items, isLoading, loadItems } = useServerTable<{ id: number }>('admin.tags', 0)

      // When
      await loadItems({ page: 1 })

      // Then
      expect(isLoading.value).toBe(false)
      expect(items.value).toEqual([])

      consoleError.mockRestore()
      get.mockRestore()
    })
  })
})
