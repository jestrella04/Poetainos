import { describe, expect, it, vi, beforeEach } from 'vitest'
import axios from 'axios'
import { usePaginatedList } from '../usePaginatedList'

beforeEach(() => {
  vi.restoreAllMocks()
})

describe('usePaginatedList', () => {
  describe('loadFirstPage', () => {
    it('replaces the items with the first page and remembers the next page', async () => {
      // Given
      vi.spyOn(axios, 'get').mockResolvedValueOnce({
        data: { data: [{ id: 1 }], next_page_url: '/comments?page=2' }
      })
      const { items, next, fetched, update, loadFirstPage } = usePaginatedList<{ id: number }>()
      update([{ id: 9 }], null)

      // When
      const isLoaded = await loadFirstPage('/comments')

      // Then
      expect(isLoaded).toBe(true)
      expect(items.value).toEqual([{ id: 1 }])
      expect(next.value).toBe('/comments?page=2')
      expect(fetched.value).toBe(true)
    })

    it('keeps the current items and reports failure when the request fails', async () => {
      // Given
      vi.spyOn(axios, 'get').mockRejectedValueOnce(new Error('network error'))
      const { items, update, loadFirstPage } = usePaginatedList<{ id: number }>()
      update([{ id: 9 }], null)

      // When
      const isLoaded = await loadFirstPage('/comments')

      // Then
      expect(isLoaded).toBe(false)
      expect(items.value).toEqual([{ id: 9 }])
    })
  })
})
