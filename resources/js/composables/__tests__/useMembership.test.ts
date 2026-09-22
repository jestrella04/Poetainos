import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import type { ComposerTranslation } from 'vue-i18n'
import { useMembership } from '../useMembership'

const { membershipDuration, membershipMessage } = useMembership()

// Mirrors the `{count} singular | {count} plural` pipe syntax vue-i18n
// resolves from the real es.json messages, so the fake stays representative
// of what members actually see.
const t = ((key: string, named?: Record<string, unknown>, plural?: number) => {
  if (key === 'accounts.member-since-today') {
    return 'menos de un día'
  }

  if (key === 'main.and') {
    return 'y'
  }

  const unit = key.replace('accounts.member-since-', '').replace(/s$/, '')
  const count = plural ?? (named?.count as number)

  return `${count} ${unit}${count === 1 ? '' : 's'}`
}) as ComposerTranslation

beforeEach(() => {
  vi.useFakeTimers()
  vi.setSystemTime(new Date('2026-09-22T12:00:00Z'))
})

afterEach(() => {
  vi.useRealTimers()
})

describe('membershipDuration', () => {
  it('combines years, months and days with a conjunction before the last part', () => {
    // Given
    const since = '2024-06-17T12:00:00Z'

    // When
    const result = membershipDuration(since, t)

    // Then
    expect(result).toBe('2 years, 3 months y 5 days')
  })

  it('omits zero-valued units', () => {
    // Given
    const since = '2026-08-22T12:00:00Z'

    // When
    const result = membershipDuration(since, t)

    // Then
    expect(result).toBe('1 month')
  })

  it('falls back to a same-day message when no full unit has elapsed', () => {
    // Given
    const since = '2026-09-22T06:00:00Z'

    // When
    const result = membershipDuration(since, t)

    // Then
    expect(result).toBe('menos de un día')
  })
})

describe('membershipMessage', () => {
  it('returns the new-member message under 30 days', () => {
    expect(membershipMessage('2026-09-10T12:00:00Z', t)).toBe(
      t('accounts.member-since-message-new')
    )
  })

  it('returns the growing message between 30 days and a year', () => {
    expect(membershipMessage('2026-06-01T12:00:00Z', t)).toBe(
      t('accounts.member-since-message-growing')
    )
  })

  it('returns the established message between one and three years', () => {
    expect(membershipMessage('2024-09-22T12:00:00Z', t)).toBe(
      t('accounts.member-since-message-established')
    )
  })

  it('returns the veteran message from three years onward', () => {
    expect(membershipMessage('2020-01-01T12:00:00Z', t)).toBe(
      t('accounts.member-since-message-veteran')
    )
  })
})
