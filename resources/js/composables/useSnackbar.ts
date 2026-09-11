import type { SnackBarState } from './keys'

/**
 * Cross-navigation snackbar messages, persisted via sessionStorage so a
 * message set before an Inertia visit survives to the next page.
 */
export function useSnackbar() {
  function setSnackBar(snack: Partial<SnackBarState> = {}): void {
    sessionStorage.setItem('snack', JSON.stringify(snack))
  }

  function getSnackBar(): Partial<SnackBarState> | null {
    const stored = sessionStorage.getItem('snack')
    sessionStorage.removeItem('snack')
    return stored === null ? null : (JSON.parse(stored) as Partial<SnackBarState>)
  }

  return { setSnackBar, getSnackBar }
}
