import { describe, expect, it, vi } from 'vitest'

const { visitMock } = vi.hoisted(() => ({ visitMock: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
  router: { visit: visitMock }
}))

import { useInertiaVisit } from '../useInertiaVisit'

describe('useInertiaVisit', () => {
  it('does not visit when href is undefined', () => {
    // Given
    const { visit } = useInertiaVisit({ href: undefined })

    // When
    visit()

    // Then
    expect(visitMock).not.toHaveBeenCalled()
  })

  it('does not visit when href is an empty string', () => {
    // Given
    const { visit } = useInertiaVisit({ href: '' })

    // When
    visit()

    // Then
    expect(visitMock).not.toHaveBeenCalled()
  })

  it('defaults to a get request when no method is given', () => {
    // Given
    const { visit } = useInertiaVisit({ href: '/writings/1' })

    // When
    visit()

    // Then
    expect(visitMock).toHaveBeenCalledWith('/writings/1', { method: 'get' })
  })

  it('forwards the method and data when provided', () => {
    // Given
    const { visit } = useInertiaVisit({
      href: '/writings/1',
      method: 'delete',
      data: { reason: 'spam' }
    })

    // When
    visit()

    // Then
    expect(visitMock).toHaveBeenCalledWith('/writings/1', {
      method: 'delete',
      data: { reason: 'spam' }
    })
  })
})
