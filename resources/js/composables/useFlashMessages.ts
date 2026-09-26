import { onBeforeUnmount, onMounted, provide, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import type { FlashData } from '@inertiajs/core'
import { snackBarKey } from './keys'
import type { SnackBarState } from './keys'

/**
 * Provides the layout's snackbar and shows in it the messages flashed with a
 * response (Inertia::flash() on the server) or by the page (router.flash()).
 */
export function useFlashMessages(): void {
  const page = usePage()
  const snackBar = reactive<SnackBarState>({
    active: false,
    avatar: '/images/logo.svg',
    color: 'primary',
    timeout: 6000,
    message: ''
  })

  provide(snackBarKey, snackBar)

  function showFlash(flash: FlashData): void {
    if (flash.message === undefined || flash.message === '') {
      return
    }

    snackBar.message = flash.message
    snackBar.color = flash.color ?? 'primary'
    snackBar.active = true
  }

  let stopListening: () => void = () => undefined

  onMounted(() => {
    // The first page's flash arrived with the HTML; later ones announce themselves
    showFlash(page.flash)
    stopListening = router.on('flash', (event) => {
      showFlash(event.detail.flash)
    })
  })

  onBeforeUnmount(() => {
    stopListening()
  })
}
