import { onBeforeUnmount, onMounted, provide, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { snackBarKey } from './keys'
import type { SnackBarState } from './keys'
import { useSnackbar } from './useSnackbar'
import { useTypeGuards } from './useTypeGuards'

/**
 * Provides the layout's snackbar and fills it with the flash messages set
 * before a visit, either client side (useSnackbar) or by the server.
 */
export function useFlashMessages(): { showFlashMessages: () => void } {
  const page = usePage()
  const { isEmpty, strNullOrEmpty } = useTypeGuards()
  const { getSnackBar } = useSnackbar()
  const snackBar = reactive<SnackBarState>({
    active: false,
    avatar: '/images/logo.svg',
    color: 'info',
    timeout: 6000,
    message: ''
  })

  provide(snackBarKey, snackBar)

  function showFlashMessages(): void {
    const snack = getSnackBar()
    const flash = page.props.flash.message

    // Check for client side flash messages
    if (snack !== null && !isEmpty(snack)) {
      snackBar.message = snack.message ?? snackBar.message
      snackBar.active = snack.active ?? snackBar.active
      snackBar.color = snack.color ?? snackBar.color
    }

    // Check for server side flash messages
    if (flash !== null && !strNullOrEmpty(flash)) {
      snackBar.message = flash
      snackBar.active = true
      snackBar.color = 'primary'
    }
  }

  let stopListeningForNavigation: () => void = () => undefined

  onMounted(() => {
    showFlashMessages()

    // Flash messages arrive with a navigation; re-reading them on every re-render
    // would bring back a snackbar the user already dismissed.
    stopListeningForNavigation = router.on('navigate', () => {
      showFlashMessages()
    })
  })

  onBeforeUnmount(() => {
    stopListeningForNavigation()
  })

  return { showFlashMessages }
}
