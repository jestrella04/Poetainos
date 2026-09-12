import { describe, expect, it, vi } from 'vitest'

const { visitMock } = vi.hoisted(() => ({ visitMock: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
  router: { visit: visitMock }
}))

import { useInertiaVisit } from '../useInertiaVisit'

describe('useInertiaVisit', () => {
  it('does not visit when href is undefined', () => {
    const { visit } = useInertiaVisit({ href: undefined })

    visit()

    expect(visitMock).not.toHaveBeenCalled()
  })

  it('does not visit when href is an empty string', () => {
    const { visit } = useInertiaVisit({ href: '' })

    visit()

    expect(visitMock).not.toHaveBeenCalled()
  })

  it('defaults to a get request when no method is given', () => {
    const { visit } = useInertiaVisit({ href: '/writings/1' })

    visit()

    expect(visitMock).toHaveBeenCalledWith('/writings/1', { method: 'get' })
  })

  it('forwards the method and data when provided', () => {
    const { visit } = useInertiaVisit({
      href: '/writings/1',
      method: 'delete',
      data: { reason: 'spam' }
    })

    visit()

    expect(visitMock).toHaveBeenCalledWith('/writings/1', {
      method: 'delete',
      data: { reason: 'spam' }
    })
  })
})
