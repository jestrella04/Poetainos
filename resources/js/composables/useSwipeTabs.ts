import { defaultDocument, useSwipe } from '@vueuse/core'
import type { UseSwipeDirection } from '@vueuse/core'

/**
 * Lets a horizontal swipe move to the next/previous tab of a tab bar, in
 * `tabOrder`. Does nothing for a page without tabs.
 */
export function useSwipeTabs(config: {
  tabOrder: string[]
  currentTab: () => string
  swipeTarget?: HTMLElement
}): void {
  function clickTab(tabName: string): void {
    document.querySelector<HTMLElement>(`.v-tab[value="${tabName}"]`)?.click()
  }

  function goToAdjacentTab(step: 1 | -1): void {
    const currentIndex = config.tabOrder.indexOf(config.currentTab())
    const targetTab = config.tabOrder[currentIndex + step]

    if (currentIndex === -1 || targetTab === undefined) {
      return
    }

    clickTab(targetTab)
  }

  if (config.tabOrder.length === 0) {
    return
  }

  useSwipe(config.swipeTarget ?? defaultDocument?.body, {
    passive: true,
    onSwipeEnd(_event: TouchEvent, direction: UseSwipeDirection) {
      if (direction === 'left') {
        goToAdjacentTab(1)
      } else if (direction === 'right') {
        goToAdjacentTab(-1)
      }
    }
  })
}
