import { describe, expect, it, vi, beforeEach } from 'vitest'
import { nextTick } from 'vue'
import { useLogEntries } from '../useLogEntries'
import {
  holdNextRequest,
  queueOutcome,
  resetFakeRequests,
  sentRequests
} from './support/fakeInertiaRequests'

vi.mock('@inertiajs/vue3', async () => {
  const { fakeUseHttp } = await import('./support/fakeInertiaRequests')

  return { useHttp: fakeUseHttp, router: { flash: vi.fn() } }
})

function entry(message: string) {
  return { level: 'error', environment: 'production', date: null, message, details: '' }
}

beforeEach(() => {
  resetFakeRequests()
  vi.stubGlobal(
    'route',
    vi.fn(
      (name: string, params: Record<string, string | number>) =>
        `${name}?${new URLSearchParams(Object.entries(params).map(([key, value]) => [key, String(value)])).toString()}`
    )
  )
})

describe('useLogEntries', () => {
  describe('reload', () => {
    it('loads the newest entries of the file with only the filters that are set', async () => {
      // Given
      queueOutcome({ data: { entries: [entry('newest')], before: 120 } })
      const { level, entries, hasOlder, reload } = useLogEntries('laravel.log')
      level.value = 'error'
      await nextTick()
      resetFakeRequests()
      queueOutcome({ data: { entries: [entry('newest')], before: 120 } })

      // When
      await reload()

      // Then
      expect(sentRequests).toEqual([
        expect.objectContaining({ url: 'admin.logs.entries?file=laravel.log&level=error' })
      ])
      expect(entries.value).toEqual([entry('newest')])
      expect(hasOlder.value).toBe(true)
    })
  })

  describe('loadOlder', () => {
    it('appends the page that ends where the previous one started', async () => {
      // Given
      queueOutcome({ data: { entries: [entry('newest')], before: 120 } })
      const { entries, hasOlder, reload, loadOlder } = useLogEntries('laravel.log')
      await reload()
      queueOutcome({ data: { entries: [entry('oldest')], before: null } })

      // When
      await loadOlder()

      // Then
      expect(sentRequests.at(-1)?.url).toBe('admin.logs.entries?file=laravel.log&before=120')
      expect(entries.value).toEqual([entry('newest'), entry('oldest')])
      expect(hasOlder.value).toBe(false)
    })

    it('keeps the loaded entries when the request fails', async () => {
      // Given
      queueOutcome({ data: { entries: [entry('newest')], before: 120 } })
      const { entries, hasOlder, isLoading, reload, loadOlder } = useLogEntries('laravel.log')
      await reload()
      queueOutcome({ failure: 'network' })

      // When
      await loadOlder()

      // Then
      expect(entries.value).toEqual([entry('newest')])
      expect(hasOlder.value).toBe(true)
      expect(isLoading.value).toBe(false)
    })
  })

  it('ignores a page that arrives after the filters changed', async () => {
    // Given
    const { entries, reload } = useLogEntries('laravel.log')
    const releaseStaleRequest = holdNextRequest()
    const staleRequest = reload()
    queueOutcome({ data: { entries: [entry('filtered')], before: null } })
    queueOutcome({ data: { entries: [entry('stale')], before: null } })

    // When
    await reload()
    releaseStaleRequest()
    await staleRequest

    // Then
    expect(entries.value).toEqual([entry('filtered')])
  })
})
