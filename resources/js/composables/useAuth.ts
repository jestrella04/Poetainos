import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTypeGuards } from './useTypeGuards'

const page = computed(() => usePage())

/**
 * Current-user auth state, sourced from the shared Inertia page props.
 */
export function useAuth() {
  const { strNullOrEmpty } = useTypeGuards()

  function auth(): boolean {
    const authProps = page.value.props.auth
    return authProps.user !== null && !strNullOrEmpty(authProps.user.username)
  }

  function authUser() {
    return page.value.props.auth.user
  }

  function admin(): boolean {
    return page.value.props.auth.admin === true
  }

  function canEdit(author: { username: string }): boolean {
    const user = authUser()

    if (user === null || strNullOrEmpty(user.username)) {
      return false
    }

    return user.username === author.username || admin()
  }

  return { auth, authUser, admin, canEdit }
}
