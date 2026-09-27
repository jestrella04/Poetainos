import { describe, expect, it, vi, beforeEach } from 'vitest'
import { defineComponent, h, inject } from 'vue'
import { mount } from '@vue/test-utils'
import { useFlashMessages } from '../useFlashMessages'
import { snackBarKey } from '../keys'
import type { SnackBarState } from '../keys'

type FlashListener = (event: { detail: { flash: Record<string, string> } }) => void

const mocks = vi.hoisted(() => {
  const page: { flash: Record<string, string> } = { flash: {} }
  const listeners: Record<string, FlashListener> = {}

  return { page, listeners }
})

vi.mock('@inertiajs/vue3', () => ({
  usePage: () => mocks.page,
  router: {
    on: (
      name: string,
      listener: (event: { detail: { flash: Record<string, string> } }) => void
    ) => {
      mocks.listeners[name] = listener
      return () => undefined
    }
  }
}))

function mountLayout(): SnackBarState {
  let snackBar: SnackBarState | undefined

  const child = defineComponent({
    setup() {
      snackBar = inject(snackBarKey)
      return () => h('div')
    }
  })

  mount(
    defineComponent({
      setup() {
        useFlashMessages()
        return () => h(child)
      }
    })
  )

  return snackBar!
}

beforeEach(() => {
  mocks.page.flash = {}
  for (const name of Object.keys(mocks.listeners)) {
    delete mocks.listeners[name]
  }
})

describe('useFlashMessages', () => {
  it('shows the message flashed with the first page', () => {
    // Given
    mocks.page.flash = { message: 'accounts.welcome-back' }

    // When
    const snackBar = mountLayout()

    // Then
    expect(snackBar).toMatchObject({
      active: true,
      message: 'accounts.welcome-back',
      color: 'primary'
    })
  })

  it('shows each message flashed afterwards, in its color', () => {
    // Given
    const snackBar = mountLayout()

    // When
    mocks.listeners.flash?.({
      detail: { flash: { message: 'users.user-blocked', color: 'success' } }
    })

    // Then
    expect(snackBar).toMatchObject({
      active: true,
      message: 'users.user-blocked',
      color: 'success'
    })
  })

  it('stays closed when nothing was flashed', () => {
    // When
    const snackBar = mountLayout()

    // Then
    expect(snackBar.active).toBe(false)
  })
})
