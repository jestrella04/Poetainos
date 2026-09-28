import { describe, expect, it } from 'vitest'
import { useFormatting } from '../useFormatting'

const { formatCount, fileSize } = useFormatting()

describe('formatCount', () => {
  it('separates thousands with a dot, including on 4-digit numbers', () => {
    expect(formatCount(980)).toBe('980')
    expect(formatCount(3412)).toBe('3.412')
    expect(formatCount(1234567)).toBe('1.234.567')
  })
})

describe('fileSize', () => {
  it('shows the size in the largest binary unit that keeps it above one', () => {
    expect(fileSize(0)).toBe('0 byte')
    expect(fileSize(980)).toBe('980 byte')
    expect(fileSize(1536)).toBe('1,5 kB')
    expect(fileSize(22_649_242)).toBe('21,6 MB')
  })
})
