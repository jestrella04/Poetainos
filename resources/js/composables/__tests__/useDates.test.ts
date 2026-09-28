import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest'
import { createSSRApp, defineComponent, h, nextTick } from 'vue'
import { renderToString } from 'vue/server-renderer'
import { mount } from '@vue/test-utils'

// Whether the page has hydrated is shared module state, so each test loads a fresh copy
async function loadUseDates(): Promise<typeof import('../useDates').useDates> {
  return (await import('../useDates')).useDates
}

beforeEach(() => {
  vi.resetModules()
})

afterEach(() => {
  vi.restoreAllMocks()
})

describe('toLocaleDateTime', () => {
  it('formats the date and time of day in the server time zone before hydration', async () => {
    // Given
    const { toLocaleDateTime } = (await loadUseDates())()
    const formatSpy = vi.spyOn(Date.prototype, 'toLocaleString')

    // When
    const result = toLocaleDateTime('2026-09-26T22:44:27+00:00')

    // Then
    expect(formatSpy).toHaveBeenCalledWith(
      'es-DO',
      expect.objectContaining({ dateStyle: 'medium', timeStyle: 'medium', timeZone: 'UTC' })
    )
    expect(result).toContain('26')
    expect(result).toContain('10:44:27')
  })
})

describe('toLocaleDate', () => {
  it('formats in the server time zone until the page hydrates, so SSR and the browser render the same day', async () => {
    // Given
    const { toLocaleDate } = (await loadUseDates())()
    const formatSpy = vi.spyOn(Date.prototype, 'toLocaleDateString')

    // When
    const result = toLocaleDate('2026-09-20T01:00:00Z')

    // Then
    expect(formatSpy).toHaveBeenCalledWith('es-DO', expect.objectContaining({ timeZone: 'UTC' }))
    expect(result).toContain('20')
  })

  it('switches to the viewer time zone once a component has mounted', async () => {
    // Given
    const useDates = await loadUseDates()
    const Host = defineComponent({
      setup() {
        const { toLocaleDate } = useDates()
        return () => h('span', toLocaleDate('2026-09-20T01:00:00Z'))
      }
    })
    const formatSpy = vi.spyOn(Date.prototype, 'toLocaleDateString')

    // When
    mount(Host)
    await nextTick()

    // Then
    expect(formatSpy).toHaveBeenLastCalledWith(
      'es-DO',
      expect.objectContaining({ timeZone: undefined })
    )
  })

  it('keeps the server time zone during server rendering, where nothing mounts', async () => {
    // Given
    const useDates = await loadUseDates()
    const Host = defineComponent({
      setup() {
        const { toLocaleDate } = useDates()
        return () => h('span', toLocaleDate('2026-09-20T01:00:00Z'))
      }
    })
    const formatSpy = vi.spyOn(Date.prototype, 'toLocaleDateString')

    // When
    await renderToString(createSSRApp(Host))

    // Then
    expect(formatSpy).toHaveBeenLastCalledWith(
      'es-DO',
      expect.objectContaining({ timeZone: 'UTC' })
    )
  })
})
