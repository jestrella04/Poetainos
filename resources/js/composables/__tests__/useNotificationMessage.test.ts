import { describe, expect, it } from 'vitest'
import type { ComposerTranslation } from 'vue-i18n'
import { useNotificationMessage } from '../useNotificationMessage'

const { notificationMessage } = useNotificationMessage()
const t = ((key: string) => key) as ComposerTranslation

describe('notificationMessage', () => {
  it('returns null for an unrecognized notification type', () => {
    const message = notificationMessage(
      { type: 'App\\Notifications\\Unknown', notifier_user: { username: 'jane' } },
      t
    )

    expect(message).toBeNull()
  })

  it('translates a known notification type', () => {
    const message = notificationMessage(
      { type: 'App\\Notifications\\WritingFeatured', notifier_user: { username: 'jane' } },
      t
    )

    expect(message).toBe('writings.writing-awarded')
  })
})
