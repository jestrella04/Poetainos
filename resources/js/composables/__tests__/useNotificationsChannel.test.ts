import { describe, expect, it, vi, afterEach } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { useNotificationsChannel } from '../useNotificationsChannel'

const mocks = vi.hoisted(() => ({
  listen: vi.fn(),
  privateChannel: vi.fn(),
  disconnect: vi.fn(),
  constructed: vi.fn()
}))

vi.mock('laravel-echo', () => ({
  default: class {
    constructor(options: unknown) {
      mocks.constructed(options)
    }

    private = mocks.privateChannel.mockReturnValue({ listen: mocks.listen })
    disconnect = mocks.disconnect
  }
}))

vi.mock('pusher-js', () => ({ default: class {} }))

function hostFor(userId: number | null, onUnreadCount: (unread: number) => void) {
  return defineComponent({
    setup() {
      useNotificationsChannel(() => userId, onUnreadCount)

      return () => h('div')
    }
  })
}

afterEach(() => {
  vi.clearAllMocks()
})

describe('useNotificationsChannel', () => {
  it("listens on the user's private notifications channel once mounted", () => {
    // When
    mount(hostFor(7, vi.fn()))

    // Then
    expect(mocks.constructed).toHaveBeenCalledExactlyOnceWith(
      expect.objectContaining({ broadcaster: 'reverb', enabledTransports: ['ws', 'wss'] })
    )
    expect(mocks.privateChannel).toHaveBeenCalledExactlyOnceWith('notifications.7')
    expect(mocks.listen).toHaveBeenCalledExactlyOnceWith('NotificationEvent', expect.any(Function))
  })

  it('reports the unread count the server sends', () => {
    // Given
    const onUnreadCount = vi.fn()
    mount(hostFor(7, onUnreadCount))
    const [, handler] = mocks.listen.mock.calls[0] as [string, (payload: unknown) => void]

    // When
    handler({ user_id: 7, notifications: { unread: 4, total: 9 } })

    // Then
    expect(onUnreadCount).toHaveBeenCalledExactlyOnceWith(4)
  })

  it('does not connect for a guest', () => {
    // When
    mount(hostFor(null, vi.fn()))

    // Then
    expect(mocks.constructed).not.toHaveBeenCalled()
  })

  it('closes the connection when the component is unmounted', () => {
    // Given
    const wrapper = mount(hostFor(7, vi.fn()))

    // When
    wrapper.unmount()

    // Then
    expect(mocks.disconnect).toHaveBeenCalledOnce()
  })

  it('has nothing to close when it never connected', () => {
    // Given
    const wrapper = mount(hostFor(null, vi.fn()))

    // When
    wrapper.unmount()

    // Then
    expect(mocks.disconnect).not.toHaveBeenCalled()
  })
})
