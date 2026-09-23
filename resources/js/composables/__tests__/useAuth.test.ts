import type { InertiaConfig } from '@inertiajs/core'
import { beforeEach, describe, expect, it, vi } from 'vitest'

type AuthProps = Pick<InertiaConfig['sharedPageProps']['auth'], 'user' | 'admin'>

// useAuth caches usePage() in a module-level computed, so the mock hands out
// one stable page object and each test swaps its auth props in place.
const { page } = vi.hoisted(() => {
  const auth: AuthProps = { user: null, admin: false }

  return { page: { props: { auth } } }
})

vi.mock('@inertiajs/vue3', () => ({
  usePage: () => page
}))

import { useAuth } from '../useAuth'

const { isAuthenticated, canEdit } = useAuth()

function signInAs(username: string, isAdmin = false): void {
  page.props.auth.user = { id: 1, username, name: username, avatar: null }
  page.props.auth.admin = isAdmin
}

beforeEach(() => {
  page.props.auth.user = null
  page.props.auth.admin = false
})

describe('isAuthenticated', () => {
  it('returns true when a user with a username is signed in', () => {
    // Given
    signInAs('ana')

    // Then
    expect(isAuthenticated()).toBe(true)
  })

  it('returns false for a guest', () => {
    // Then
    expect(isAuthenticated()).toBe(false)
  })

  it('returns false when the signed-in user has a blank username', () => {
    // Given
    signInAs('  ')

    // Then
    expect(isAuthenticated()).toBe(false)
  })
})

describe('canEdit', () => {
  it('lets the author edit their own content', () => {
    // Given
    signInAs('ana')

    // Then
    expect(canEdit({ username: 'ana' })).toBe(true)
  })

  it("refuses a regular user editing someone else's content", () => {
    // Given
    signInAs('ana')

    // Then
    expect(canEdit({ username: 'luis' })).toBe(false)
  })

  it("lets an admin edit anyone's content", () => {
    // Given
    signInAs('admin', true)

    // Then
    expect(canEdit({ username: 'luis' })).toBe(true)
  })

  it('refuses a guest', () => {
    // Then
    expect(canEdit({ username: 'ana' })).toBe(false)
  })

  it('refuses a signed-in user with a blank username even when flagged as admin', () => {
    // Given
    signInAs('', true)

    // Then
    expect(canEdit({ username: '' })).toBe(false)
  })
})
