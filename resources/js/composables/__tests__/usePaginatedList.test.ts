import { describe, expect, it, vi, beforeEach } from 'vitest'
import { usePaginatedList } from '../usePaginatedList'
import {
  holdNextRequest,
  queueOutcome,
  resetFakeRequests,
  sentRequests
} from './support/fakeInertiaRequests'

vi.mock('@inertiajs/vue3', async () => {
  const { fakeUseHttp } = await import('./support/fakeInertiaRequests')

  return { useHttp: fakeUseHttp }
})

beforeEach(() => {
  resetFakeRequests()
})

describe('usePaginatedList', () => {
  describe('loadFirstPage', () => {
    it('replaces the items with the first page and remembers the next page', async () => {
      // Given
      queueOutcome({ data: { data: [{ id: 1 }], next_page_url: '/comments?page=2' } })
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
      queueOutcome({ failure: 'network' })
      const { items, update, loadFirstPage } = usePaginatedList<{ id: number }>()
      update([{ id: 9 }], null)

      // When
      const isLoaded = await loadFirstPage('/comments')

      // Then
      expect(isLoaded).toBe(false)
      expect(items.value).toEqual([{ id: 9 }])
    })

    it('lets a newer load win over an older one still in flight', async () => {
      // Given
      const releaseOlderLoad = holdNextRequest()
      // Outcomes are handed out as requests settle: the newer load settles first
      queueOutcome({ data: { data: [{ id: 2 }], next_page_url: null } })
      queueOutcome({ failure: 'network' })
      const { items, loadFirstPage } = usePaginatedList<{ id: number }>()
      const olderLoad = loadFirstPage('/comments')

      // When
      const newerLoad = loadFirstPage('/comments')
      releaseOlderLoad()

      // Then
      expect(await olderLoad).toBe(true)
      expect(await newerLoad).toBe(true)
      expect(items.value).toEqual([{ id: 2 }])
    })
  })

  describe('loadMore', () => {
    it('reports empty when there is no next page to load', async () => {
      // Given
      const { loadMore } = usePaginatedList<{ id: number }>()
      const done = vi.fn()

      // When
      await loadMore({ done })

      // Then
      expect(done).toHaveBeenCalledWith('empty')
      expect(sentRequests).toEqual([])
    })

    it('appends the next page of items and reports ok on success', async () => {
      // Given
      queueOutcome({ data: { data: [{ id: 2 }], next_page_url: null } })
      const { items, next, update, loadMore } = usePaginatedList<{ id: number }>()
      update([{ id: 1 }], '/writings?page=2')
      const done = vi.fn()

      // When
      await loadMore({ done })

      // Then
      expect(sentRequests).toEqual([expect.objectContaining({ url: '/writings?page=2' })])
      expect(items.value).toEqual([{ id: 1 }, { id: 2 }])
      expect(next.value).toBe('')
      expect(done).toHaveBeenCalledWith('ok')
    })

    it('reports error when the pagination request fails', async () => {
      // Given
      queueOutcome({ failure: 'http' })
      const { update, loadMore } = usePaginatedList<{ id: number }>()
      update([], '/writings?page=2')
      const done = vi.fn()

      // When
      await loadMore({ done })

      // Then
      expect(done).toHaveBeenCalledWith('error')
    })
  })
})
