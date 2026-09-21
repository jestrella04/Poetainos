import { describe, expect, it, vi, beforeEach } from 'vitest'
import { useSwipe } from '@vueuse/core'
import { useSwipeTabs } from '../useSwipeTabs'

vi.mock('@vueuse/core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@vueuse/core')>()
  return { ...actual, useSwipe: vi.fn() }
})

type SwipeOptions = { onSwipeEnd?: (event: TouchEvent, direction: string) => void }

let swipeOptions: SwipeOptions = {}

beforeEach(() => {
  document.body.innerHTML = ''
  vi.clearAllMocks()
  swipeOptions = {}
  vi.mocked(useSwipe).mockImplementation((_target, options) => {
    swipeOptions = options as SwipeOptions
    return {} as ReturnType<typeof useSwipe>
  })
})

describe('useSwipeTabs', () => {
  it('clicks the next tab in tabOrder on a left swipe', () => {
    // Given
    document.body.innerHTML = '<button class="v-tab" value="popular"></button>'
    const popularTab = document.querySelector<HTMLElement>('.v-tab[value="popular"]')
    expect(popularTab).not.toBeNull()
    const click = vi.fn()
    popularTab!.click = click
    useSwipeTabs({ tabOrder: ['latest', 'popular'], currentTab: () => 'latest' })

    // When
    swipeOptions.onSwipeEnd?.({} as TouchEvent, 'left')

    // Then
    expect(click).toHaveBeenCalledOnce()
  })

  it('clicks the previous tab in tabOrder on a right swipe', () => {
    // Given
    document.body.innerHTML = '<button class="v-tab" value="latest"></button>'
    const latestTab = document.querySelector<HTMLElement>('.v-tab[value="latest"]')
    expect(latestTab).not.toBeNull()
    const click = vi.fn()
    latestTab!.click = click
    useSwipeTabs({ tabOrder: ['latest', 'popular'], currentTab: () => 'popular' })

    // When
    swipeOptions.onSwipeEnd?.({} as TouchEvent, 'right')

    // Then
    expect(click).toHaveBeenCalledOnce()
  })

  it('does nothing when swiping past the last tab', () => {
    // Given
    useSwipeTabs({ tabOrder: ['latest', 'popular'], currentTab: () => 'popular' })

    // Then
    expect(() => swipeOptions.onSwipeEnd?.({} as TouchEvent, 'left')).not.toThrow()
  })

  it('does not listen for swipes on a page without tabs', () => {
    // When
    useSwipeTabs({ tabOrder: [], currentTab: () => 'latest' })

    // Then
    expect(useSwipe).not.toHaveBeenCalled()
  })
})
