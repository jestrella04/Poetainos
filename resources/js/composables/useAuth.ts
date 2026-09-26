import { usePage } from '@inertiajs/vue3'
import { useTypeGuards } from './useTypeGuards'

/**
 * Current-user auth state, sourced from the shared Inertia page props. Each
 * check reads the current page, so it stays right across visits.
 */
export function useAuth() {
  const page = usePage()
  const { isBlank } = useTypeGuards()

  function isAuthenticated(): boolean {
    const authProps = page.props.auth
    return authProps.user !== null && !isBlank(authProps.user.username)
  }

  function authUser() {
    return page.props.auth.user
  }

  function isAdmin(): boolean {
    return page.props.auth.admin === true
  }

  function canEdit(author: { username: string }): boolean {
    const user = authUser()

    if (user === null || isBlank(user.username)) {
      return false
    }

    return user.username === author.username || isAdmin()
  }

  return { isAuthenticated, authUser, isAdmin, canEdit }
}
