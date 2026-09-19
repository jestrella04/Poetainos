import { describe, expect, it, vi, beforeEach } from 'vitest'
import { reactive, nextTick } from 'vue'
import axios from 'axios'
import { useReactionToggle } from '../useReactionToggle'

function buildSource(overrides: Partial<Parameters<typeof useReactionToggle>[0]> = {}) {
  return reactive({
    count: 3,
    isActive: false,
    postUrl: '/likes/writing/1/store',
    canReact: true,
    ...overrides
  })
}

function buildOptions(isAuthenticated = true) {
  return { isAuthenticated: () => isAuthenticated, onUnauthenticated: vi.fn() }
}

beforeEach(() => {
  vi.restoreAllMocks()
})

describe('useReactionToggle', () => {
  it('adopts the returned count and becomes active on store', async () => {
    // Given
    const post = vi.spyOn(axios, 'post').mockResolvedValue({ data: { method: 'store', count: 4 } })
    const { count, isActive, toggle } = useReactionToggle(buildSource(), buildOptions())

    // When
    await toggle()

    // Then
    expect(post).toHaveBeenCalledWith('/likes/writing/1/store')
    expect(count.value).toBe(4)
    expect(isActive.value).toBe(true)
  })

  it('adopts the returned count and becomes inactive on destroy', async () => {
    // Given
    vi.spyOn(axios, 'post').mockResolvedValue({ data: { method: 'destroy', count: 2 } })
    const { count, isActive, toggle } = useReactionToggle(
      buildSource({ isActive: true }),
      buildOptions()
    )

    // When
    await toggle()

    // Then
    expect(count.value).toBe(2)
    expect(isActive.value).toBe(false)
  })

  it('prompts a login instead of posting when logged out', async () => {
    // Given
    const post = vi.spyOn(axios, 'post')
    const options = buildOptions(false)
    const { toggle } = useReactionToggle(buildSource(), options)

    // When
    await toggle()

    // Then
    expect(options.onUnauthenticated).toHaveBeenCalledOnce()
    expect(post).not.toHaveBeenCalled()
  })

  it('does not post when the viewer cannot react', async () => {
    // Given
    const post = vi.spyOn(axios, 'post')
    const options = buildOptions()
    const { toggle } = useReactionToggle(buildSource({ canReact: false }), options)

    // When
    await toggle()

    // Then
    expect(post).not.toHaveBeenCalled()
    expect(options.onUnauthenticated).not.toHaveBeenCalled()
  })

  it('keeps the previous state when the request fails', async () => {
    // Given
    vi.spyOn(axios, 'post').mockRejectedValue(new Error('boom'))
    const { count, isActive, isSubmitting, toggle } = useReactionToggle(
      buildSource(),
      buildOptions()
    )

    // When
    await toggle()

    // Then
    expect(count.value).toBe(3)
    expect(isActive.value).toBe(false)
    expect(isSubmitting.value).toBe(false)
  })

  it('ignores a second toggle while one is in flight', async () => {
    // Given
    const post = vi.spyOn(axios, 'post').mockResolvedValue({ data: { method: 'store', count: 4 } })
    const { toggle } = useReactionToggle(buildSource(), buildOptions())

    // When
    await Promise.all([toggle(), toggle()])

    // Then
    expect(post).toHaveBeenCalledOnce()
  })

  it('re-syncs local state when the source changes', async () => {
    // Given
    const source = buildSource()
    const { count, isActive } = useReactionToggle(source, buildOptions())

    // When
    source.count = 9
    source.isActive = true
    await nextTick()

    // Then
    expect(count.value).toBe(9)
    expect(isActive.value).toBe(true)
  })
})
