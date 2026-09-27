import { describe, expect, it, vi, beforeEach } from 'vitest'
import { reactive, nextTick } from 'vue'
import { useReactionToggle } from '../useReactionToggle'
import { queueOutcome, resetFakeRequests, sentRequests } from './support/fakeInertiaRequests'

vi.mock('@inertiajs/vue3', async () => {
  const { fakeUseHttp } = await import('./support/fakeInertiaRequests')

  return { useHttp: fakeUseHttp, router: { flash: vi.fn() } }
})

function buildSource(overrides: Partial<Parameters<typeof useReactionToggle>[0]> = {}) {
  return reactive({
    count: 3,
    isActive: false,
    postUrl: '/likes/writing/1/toggle',
    canReact: true,
    ...overrides
  })
}

function buildOptions(isAuthenticated = true) {
  return { isAuthenticated: () => isAuthenticated, onUnauthenticated: vi.fn() }
}

beforeEach(() => {
  resetFakeRequests()
})

describe('useReactionToggle', () => {
  it('adopts the returned count and becomes active', async () => {
    // Given
    queueOutcome({ data: { isActive: true, count: 4 } })
    const { count, isActive, toggle } = useReactionToggle(buildSource(), buildOptions())

    // When
    await toggle()

    // Then
    expect(sentRequests).toEqual([
      expect.objectContaining({ method: 'post', url: '/likes/writing/1/toggle' })
    ])
    expect(count.value).toBe(4)
    expect(isActive.value).toBe(true)
  })

  it('adopts the returned count and becomes inactive', async () => {
    // Given
    queueOutcome({ data: { isActive: false, count: 2 } })
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
    const options = buildOptions(false)
    const { toggle } = useReactionToggle(buildSource(), options)

    // When
    await toggle()

    // Then
    expect(options.onUnauthenticated).toHaveBeenCalledOnce()
    expect(sentRequests).toEqual([])
  })

  it('does not post when the viewer cannot react', async () => {
    // Given
    const options = buildOptions()
    const { toggle } = useReactionToggle(buildSource({ canReact: false }), options)

    // When
    await toggle()

    // Then
    expect(sentRequests).toEqual([])
    expect(options.onUnauthenticated).not.toHaveBeenCalled()
  })

  it('keeps the previous state when the request fails', async () => {
    // Given
    queueOutcome({ failure: 'http' })
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
    queueOutcome({ data: { isActive: true, count: 4 } })
    const { toggle } = useReactionToggle(buildSource(), buildOptions())

    // When
    await Promise.all([toggle(), toggle()])

    // Then
    expect(sentRequests).toHaveLength(1)
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
