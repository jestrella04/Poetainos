import { describe, expect, it } from 'vitest'
import { useRowDialog } from '../useRowDialog'

describe('useRowDialog', () => {
  it('starts closed, without a row', () => {
    // When
    const { row, isOpen } = useRowDialog<{ id: number }>()

    // Then
    expect(isOpen.value).toBe(false)
    expect(row.value).toBeNull()
  })

  it('opens about the given row', () => {
    // Given
    const { row, isOpen, open } = useRowDialog<{ id: number }>()

    // When
    open({ id: 7 })

    // Then
    expect(isOpen.value).toBe(true)
    expect(row.value).toEqual({ id: 7 })
  })

  it('opens about a new row, forgetting the previous one', () => {
    // Given
    const { row, isOpen, open } = useRowDialog<{ id: number }>()
    open({ id: 7 })

    // When
    open()

    // Then
    expect(isOpen.value).toBe(true)
    expect(row.value).toBeNull()
  })
})
