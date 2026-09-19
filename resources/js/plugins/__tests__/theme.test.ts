import { describe, expect, it } from 'vitest'
import { themes } from '../theme'

const MINIMUM_TEXT_CONTRAST = 4.5

const COLOR_PAIRS = [
  'primary',
  'secondary',
  'surface',
  'background',
  'error',
  'success',
  'info',
  'warning'
] as const

function toRelativeLuminance(hex: string): number {
  const linearChannel = (start: number): number => {
    const channel = parseInt(hex.slice(start, start + 2), 16) / 255
    return channel <= 0.03928 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4
  }

  return 0.2126 * linearChannel(1) + 0.7152 * linearChannel(3) + 0.0722 * linearChannel(5)
}

function toContrastRatio(foreground: string, background: string): number {
  const foregroundLuminance = toRelativeLuminance(foreground)
  const backgroundLuminance = toRelativeLuminance(background)

  return (
    (Math.max(foregroundLuminance, backgroundLuminance) + 0.05) /
    (Math.min(foregroundLuminance, backgroundLuminance) + 0.05)
  )
}

describe.each(Object.entries(themes))('%s theme', (_name, theme) => {
  const colors = theme.colors as Record<string, string>
  const colorOf = (token: string): string => colors[token] ?? ''

  it.each(COLOR_PAIRS)('%s text meets AA contrast on its fill', (role) => {
    // When
    const ratio = toContrastRatio(colorOf(`on-${role}`), colorOf(role))

    // Then
    expect(ratio).toBeGreaterThanOrEqual(MINIMUM_TEXT_CONTRAST)
  })

  it('on-surface-variant meets AA contrast on surface', () => {
    // When
    const ratio = toContrastRatio(colorOf('on-surface-variant'), colorOf('surface'))

    // Then
    expect(ratio).toBeGreaterThanOrEqual(MINIMUM_TEXT_CONTRAST)
  })

  it('primary meets AA contrast on surface so it works as link/text color', () => {
    // When
    const ratio = toContrastRatio(colorOf('primary'), colorOf('surface'))

    // Then
    expect(ratio).toBeGreaterThanOrEqual(MINIMUM_TEXT_CONTRAST)
  })
})
