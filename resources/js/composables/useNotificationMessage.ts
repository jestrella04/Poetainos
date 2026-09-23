import type { ComposerTranslation } from 'vue-i18n'
import { useFormatting } from './useFormatting'
import type { UserLike } from '@/types/models'

export interface NotificationLike {
  type: string
  // The user this notification is about is resolved server-side by id and
  // can come back null (e.g. the referenced user was since deleted).
  notifier_user: UserLike | null
}

/**
 * Human-readable message for a notification's type. Mirrors
 * getNotificationMessage() in app/Helpers/Helper.php — kept as a separate,
 * documented duplication rather than a shared source of truth, since this
 * project deliberately avoids adding a build-time generation layer for a
 * dozen-case list shared across two languages.
 */
export function useNotificationMessage() {
  const { userDisplayName } = useFormatting()

  function notificationMessage(
    notification: NotificationLike,
    t: ComposerTranslation
  ): string | null {
    let message: string | null = null
    // Falls back when the notifying user has since been deleted — the
    // server resolves notifier_user by id and can return null.
    const name =
      notification.notifier_user !== null ? userDisplayName(notification.notifier_user) : 'Usuario'

    switch (notification.type) {
      case 'App\\Notifications\\WritingCommented':
        message = t('comments.user-added', { name })
        break

      case 'App\\Notifications\\WritingCommentMentioned':
      case 'App\\Notifications\\WritingReplyMentioned':
        message = t('comments.user-mentioned', { name })
        break

      case 'App\\Notifications\\WritingFeatured':
        message = t('writings.writing-awarded')
        break

      case 'App\\Notifications\\WritingLiked':
        message = t('writings.user-liked', { name })
        break

      case 'App\\Notifications\\WritingReplied':
        message = t('comments.user-replied', { name })
        break

      case 'App\\Notifications\\WritingShelved':
        message = t('writings.user-shelved', { name })
        break

      case 'App\\Notifications\\CommentLiked':
        message = t('comments.user-liked', { name })
        break

      default:
        // Unknown/removed notification type — omit rather than crash the
        // notification list.
        message = null
        break
    }

    return message
  }

  return { notificationMessage }
}
