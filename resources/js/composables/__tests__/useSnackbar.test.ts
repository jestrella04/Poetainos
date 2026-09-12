import { describe, expect, it } from 'vitest'
import { useSnackbar } from '../useSnackbar'

const { setSnackBar, getSnackBar } = useSnackbar()

describe('setSnackBar / getSnackBar', () => {
  it('round-trips a snack through sessionStorage and clears it after reading', () => {
    // When
    setSnackBar({ message: 'Saved', color: 'success', active: true })

    // Then
    expect(getSnackBar()).toEqual({ message: 'Saved', color: 'success', active: true })
    expect(getSnackBar()).toBeNull()
  })
})
