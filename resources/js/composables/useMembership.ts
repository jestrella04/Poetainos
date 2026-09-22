import { differenceInCalendarDays, intervalToDuration } from 'date-fns'
import type { ComposerTranslation } from 'vue-i18n'

const NEW_MEMBER_DAYS = 30
const ESTABLISHED_MEMBER_YEARS = 1
const VETERAN_MEMBER_YEARS = 3
const DAYS_PER_YEAR = 365

/**
 * Human-readable membership duration and an accompanying inspirational
 * message for a user's account age. Mirrors the `t(key, named, plural)`
 * pluralization pattern already used across the app (see PoUsersCard.vue).
 */
export function useMembership() {
  function membershipDuration(since: string | number | Date, t: ComposerTranslation): string {
    const startDate = new Date(since)
    const duration = intervalToDuration({ start: startDate, end: new Date() })
    const years = duration.years ?? 0
    const months = duration.months ?? 0
    const days = duration.days ?? 0

    const parts = [
      years > 0 ? t('accounts.member-since-years', { count: years }, years) : null,
      months > 0 ? t('accounts.member-since-months', { count: months }, months) : null,
      days > 0 ? t('accounts.member-since-days', { count: days }, days) : null
    ].filter((part): part is string => part !== null)

    const lastPart = parts.at(-1)

    if (lastPart === undefined) {
      return t('accounts.member-since-today')
    }

    const precedingParts = parts.slice(0, -1)

    if (precedingParts.length === 0) {
      return lastPart
    }

    return `${precedingParts.join(', ')} ${t('main.and')} ${lastPart}`
  }

  function membershipMessage(since: string | number | Date, t: ComposerTranslation): string {
    const daysSince = differenceInCalendarDays(new Date(), new Date(since))

    if (daysSince < NEW_MEMBER_DAYS) {
      return t('accounts.member-since-message-new')
    }

    if (daysSince < DAYS_PER_YEAR * ESTABLISHED_MEMBER_YEARS) {
      return t('accounts.member-since-message-growing')
    }

    if (daysSince < DAYS_PER_YEAR * VETERAN_MEMBER_YEARS) {
      return t('accounts.member-since-message-established')
    }

    return t('accounts.member-since-message-veteran')
  }

  return { membershipDuration, membershipMessage }
}
