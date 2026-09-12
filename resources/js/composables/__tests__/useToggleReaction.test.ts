import { describe, expect, it, vi, beforeEach } from 'vitest'
import axios from 'axios'
import { useToggleReaction } from '../useToggleReaction'

function buildClickEvent(): MouseEvent {
  document.body.innerHTML =
    '<div class="do-like"><i class="fas fa-heart"></i><span class="target"></span></div>'
  const target = document.querySelector<HTMLElement>('.target')!
  return { target } as unknown as MouseEvent
}

beforeEach(() => {
  vi.restoreAllMocks()
})

describe('useToggleReaction', () => {
  it('does nothing when the doer ancestor is not found', async () => {
    document.body.innerHTML = '<span class="target"></span>'
    const event = { target: document.querySelector('.target') } as unknown as MouseEvent
    const post = vi.spyOn(axios, 'post')
    const { toggleReaction } = useToggleReaction()

    await toggleReaction({
      event,
      doerSelector: '.do-like',
      canReact: true,
      isAuthenticated: true,
      onUnauthenticated: vi.fn(),
      postUrl: '/likes/1',
      activeClass: 'liked',
      onCount: vi.fn()
    })

    expect(post).not.toHaveBeenCalled()
  })

  it('posts the toggle, adds the active class, and reports the count on store', async () => {
    const event = buildClickEvent()
    vi.spyOn(axios, 'post').mockResolvedValueOnce({ data: { count: 5, method: 'store' } })
    const onCount = vi.fn()
    const { toggleReaction } = useToggleReaction()

    await toggleReaction({
      event,
      doerSelector: '.do-like',
      canReact: true,
      isAuthenticated: true,
      onUnauthenticated: vi.fn(),
      postUrl: '/likes/1',
      activeClass: 'liked',
      onCount
    })

    expect(onCount).toHaveBeenCalledWith(5)
    expect(document.querySelector('.do-like')?.classList.contains('liked')).toBe(true)
  })

  it('removes the active class when the toggle destroys the reaction', async () => {
    const event = buildClickEvent()
    document.querySelector('.do-like')?.classList.add('liked')
    vi.spyOn(axios, 'post').mockResolvedValueOnce({ data: { count: 0, method: 'destroy' } })
    const { toggleReaction } = useToggleReaction()

    await toggleReaction({
      event,
      doerSelector: '.do-like',
      canReact: true,
      isAuthenticated: true,
      onUnauthenticated: vi.fn(),
      postUrl: '/likes/1',
      activeClass: 'liked',
      onCount: vi.fn()
    })

    expect(document.querySelector('.do-like')?.classList.contains('liked')).toBe(false)
  })

  it('prompts login instead of posting when the viewer is not authenticated', async () => {
    const event = buildClickEvent()
    const post = vi.spyOn(axios, 'post')
    const onUnauthenticated = vi.fn()
    const { toggleReaction } = useToggleReaction()

    await toggleReaction({
      event,
      doerSelector: '.do-like',
      canReact: false,
      isAuthenticated: false,
      onUnauthenticated,
      postUrl: '/likes/1',
      activeClass: 'liked',
      onCount: vi.fn()
    })

    expect(post).not.toHaveBeenCalled()
    expect(onUnauthenticated).toHaveBeenCalledOnce()
  })

  it('silently does nothing for an authenticated viewer who cannot react (e.g. own content)', async () => {
    const event = buildClickEvent()
    const post = vi.spyOn(axios, 'post')
    const onUnauthenticated = vi.fn()
    const { toggleReaction } = useToggleReaction()

    await toggleReaction({
      event,
      doerSelector: '.do-like',
      canReact: false,
      isAuthenticated: true,
      onUnauthenticated,
      postUrl: '/likes/1',
      activeClass: 'liked',
      onCount: vi.fn()
    })

    expect(post).not.toHaveBeenCalled()
    expect(onUnauthenticated).not.toHaveBeenCalled()
  })
})
