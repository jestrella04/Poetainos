import { head, last, toUpper, words } from 'lodash-es'
import type { UserLike } from '@/types/models'

const KARMA_MEDALS = new Map([
  ['A', 'amber-accent-4'],
  ['B', 'blue-grey-lighten-3'],
  ['C', 'deep-orange-accent-1']
])

/**
 * How a user is shown: their name, the initials standing in for a missing
 * avatar, and the medal of their karma grade.
 */
export function useUserDisplay() {
  function userDisplayName(user: UserLike): string {
    const name = user.name ?? ''

    return name !== '' ? name : user.username
  }

  function userInitials(user: UserLike): string {
    const nameParts = words(user.name ?? '', /\S+/g)

    if (nameParts.length === 0) {
      return toUpper(user.username.substring(0, 1))
    }

    const lastPart = nameParts.length > 1 ? last(nameParts) : ''

    return toUpper(`${head(nameParts)?.substring(0, 1)}${lastPart?.substring(0, 1)}`)
  }

  function karmaMedal(grade: string): string | null {
    return KARMA_MEDALS.get(grade) ?? null
  }

  return { userDisplayName, userInitials, karmaMedal }
}
