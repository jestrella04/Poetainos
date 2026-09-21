import { describe, expect, it, vi, beforeEach } from 'vitest'
import axios from 'axios'
import { useInfiniteList } from '../useInfiniteList'

beforeEach(() => {
  vi.clearAllMocks()
})

describe('useInfiniteList', () => {
  describe('loadMore', () => {
    it('reports empty when there is no next page to load', async () => {
      // Given
      const { loadMore } = useInfiniteList<{ id: number }>('writings')
      const done = vi.fn()

      // When
      await loadMore({ done })

      // Then
      expect(done).toHaveBeenCalledWith('empty')
    })

    it('appends the next page of items and reports ok on success', async () => {
      // Given
      const get = vi
        .spyOn(axios, 'get')
        .mockResolvedValueOnce({ data: { data: [{ id: 2 }], next_page_url: null } })
      const { items, next, loadMore } = useInfiniteList<{ id: number }>('writings')
      next.value = '/writings?page=2'
      const done = vi.fn()

      // When
      await loadMore({ done })

      // Then
      expect(items.value).toEqual([{ id: 2 }])
      expect(next.value).toBe('')
      expect(done).toHaveBeenCalledWith('ok')

      get.mockRestore()
    })

    it('reports error when the pagination request fails', async () => {
      // Given
      const get = vi.spyOn(axios, 'get').mockRejectedValueOnce(new Error('network error'))
      const { next, loadMore } = useInfiniteList<{ id: number }>('writings')
      next.value = '/writings?page=2'
      const done = vi.fn()

      // When
      await loadMore({ done })

      // Then
      expect(done).toHaveBeenCalledWith('error')

      get.mockRestore()
    })
  })
})
