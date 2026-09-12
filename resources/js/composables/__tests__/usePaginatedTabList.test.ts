import { describe, expect, it, vi, beforeEach } from 'vitest'
import axios from 'axios'
import { useSwipe } from '@vueuse/core'
import { usePaginatedTabList } from '../usePaginatedTabList'

vi.mock('@vueuse/core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@vueuse/core')>()
  return { ...actual, useSwipe: vi.fn() }
})

type SwipeOptions = { onSwipeEnd?: (event: TouchEvent, direction: string) => void }

beforeEach(() => {
  document.body.innerHTML = ''
  vi.clearAllMocks()
})

describe('usePaginatedTabList', () => {
  it('reports empty when there is no next page to load', async () => {
    const { loadMore } = usePaginatedTabList<{ id: number }>({
      tabOrder: ['latest', 'popular'],
      currentTab: () => 'latest',
      reloadPropKey: 'writings'
    })

    const done = vi.fn()
    await loadMore({ done })

    expect(done).toHaveBeenCalledWith('empty')
  })

  it('appends the next page of items and reports ok on success', async () => {
    const get = vi
      .spyOn(axios, 'get')
      .mockResolvedValueOnce({ data: { data: [{ id: 2 }], next_page_url: null } })

    const { items, next, loadMore } = usePaginatedTabList<{ id: number }>({
      tabOrder: ['latest', 'popular'],
      currentTab: () => 'latest',
      reloadPropKey: 'writings'
    })
    next.value = '/writings?page=2'

    const done = vi.fn()
    await loadMore({ done })

    expect(items.value).toEqual([{ id: 2 }])
    expect(next.value).toBe('')
    expect(done).toHaveBeenCalledWith('ok')

    get.mockRestore()
  })

  it('reports error when the pagination request fails', async () => {
    const get = vi.spyOn(axios, 'get').mockRejectedValueOnce(new Error('network error'))

    const { next, loadMore } = usePaginatedTabList<{ id: number }>({
      tabOrder: ['latest', 'popular'],
      currentTab: () => 'latest',
      reloadPropKey: 'writings'
    })
    next.value = '/writings?page=2'

    const done = vi.fn()
    await loadMore({ done })

    expect(done).toHaveBeenCalledWith('error')

    get.mockRestore()
  })

  it('clicks the next tab in tabOrder on a left swipe', () => {
    let swipeOptions: SwipeOptions = {}
    vi.mocked(useSwipe).mockImplementation((_target, options) => {
      swipeOptions = options as SwipeOptions
      return {} as ReturnType<typeof useSwipe>
    })

    document.body.innerHTML = '<button class="v-tab" value="popular"></button>'
    const popularTab = document.querySelector<HTMLElement>('.v-tab[value="popular"]')
    expect(popularTab).not.toBeNull()
    const click = vi.fn()
    popularTab!.click = click

    usePaginatedTabList<{ id: number }>({
      tabOrder: ['latest', 'popular'],
      currentTab: () => 'latest',
      reloadPropKey: 'writings'
    })

    swipeOptions.onSwipeEnd?.({} as TouchEvent, 'left')

    expect(click).toHaveBeenCalledOnce()
  })

  it('clicks the previous tab in tabOrder on a right swipe', () => {
    let swipeOptions: SwipeOptions = {}
    vi.mocked(useSwipe).mockImplementation((_target, options) => {
      swipeOptions = options as SwipeOptions
      return {} as ReturnType<typeof useSwipe>
    })

    document.body.innerHTML = '<button class="v-tab" value="latest"></button>'
    const latestTab = document.querySelector<HTMLElement>('.v-tab[value="latest"]')
    expect(latestTab).not.toBeNull()
    const click = vi.fn()
    latestTab!.click = click

    usePaginatedTabList<{ id: number }>({
      tabOrder: ['latest', 'popular'],
      currentTab: () => 'popular',
      reloadPropKey: 'writings'
    })

    swipeOptions.onSwipeEnd?.({} as TouchEvent, 'right')

    expect(click).toHaveBeenCalledOnce()
  })

  it('does nothing when swiping past the last tab', () => {
    let swipeOptions: SwipeOptions = {}
    vi.mocked(useSwipe).mockImplementation((_target, options) => {
      swipeOptions = options as SwipeOptions
      return {} as ReturnType<typeof useSwipe>
    })

    document.body.innerHTML = ''

    usePaginatedTabList<{ id: number }>({
      tabOrder: ['latest', 'popular'],
      currentTab: () => 'popular',
      reloadPropKey: 'writings'
    })

    expect(() => swipeOptions.onSwipeEnd?.({} as TouchEvent, 'left')).not.toThrow()
  })
})
