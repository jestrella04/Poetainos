import * as _ from 'lodash-es'
import { millify } from 'millify'
import { computed } from 'vue'
import type { App } from 'vue'
import { usePage } from '@inertiajs/vue3'
import crop from 'crop-url'
import linkifyHtml from 'linkify-html'
import 'linkify-plugin-mention'
import MarkdownIt from 'markdown-it'
import { intlFormatDistance } from 'date-fns'
import type { ComposerTranslation } from 'vue-i18n'
import { helperKey, type SnackBarState } from '../composables/keys'
import type { UserLike } from '../types/models'

const page = computed(() => usePage())

interface NotificationLike {
  type: string
  // The user this notification is about is resolved server-side by id and
  // can come back null (e.g. the referenced user was since deleted).
  notifier_user: UserLike | null
}

export class Helper {
  auth(): boolean {
    const auth = page.value.props.auth
    return auth.user !== null && !this.strNullOrEmpty(auth.user.username)
  }

  authUser() {
    return page.value.props.auth.user
  }

  admin(): boolean {
    return page.value.props.auth.admin === true
  }

  canEdit(author: { username: string }): boolean {
    const user = this.authUser()

    if (user === null || this.strNullOrEmpty(user.username)) {
      return false
    }

    return user.username === author.username || this.admin()
  }

  storage(path: string): string {
    return `/storage/${path}`
  }

  isNil(obj: unknown): obj is null | undefined {
    return _.isNil(obj)
  }

  isNull(obj: unknown): obj is null {
    return _.isNull(obj)
  }

  isEmpty(obj: unknown): boolean {
    return _.isEmpty(obj)
  }

  strNullOrEmpty(str: string | null | undefined): boolean {
    return _.isNil(str) || '' === str.trim()
  }

  userDisplayName(user: UserLike): string {
    if (!_.isNil(user.name) && '' !== user.name) {
      return user.name
    }

    return user.username
  }

  userInitials(user: UserLike): string {
    if (!_.isNil(user.name) && !_.isNil(user.last_name)) {
      return _.toUpper(`${user.name.substring(0, 1)}${user.last_name.substring(0, 1)}`)
    }

    return _.toUpper(user.username.substring(0, 1))
  }

  readable(value: number): string {
    return millify(value)
  }

  toLocaleDate(date: string | number | Date): string {
    return new Date(date).toLocaleDateString('es-DO', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  }

  relativeDate(date: string | number | Date): string {
    return intlFormatDistance(new Date(date), new Date(), { locale: 'es' })
  }

  excerpt(text: string): string {
    const len = text.length

    if (len < 400) {
      return text
    }

    return `${text.substring(0, 400)}...`
  }

  cropUrl(url: string, max = 40): string {
    return crop(url, max)
  }

  linkify(text: string): string {
    const options = {
      formatHref: {
        mention: (href: string) => `${route('users.index')}${href}`
      }
    }

    return linkifyHtml(text, options)
  }

  socialLink(user: string, network: string): string {
    let url = ''

    switch (network) {
      case 'twitter':
        url = `https://twitter.com/${user}`
        break

      case 'threads':
        url = `https://threads.net/@${user}`
        break

      case 'instagram':
        url = `https://instagram.com/${user}`
        break

      case 'facebook':
        url = `https://facebook.com/${user}`
        break

      case 'youtube':
        url = `https://youtube.com/user/${user}`
        break

      case 'goodreads':
        url = `https://www.goodreads.com/${user}`
        break

      case 'telegram':
        url = `https://t.me/${user}`
        break
    }

    return url
  }

  markdown(md: string): string {
    return MarkdownIt().render(md)
  }

  notificationMessage(notification: NotificationLike, t: ComposerTranslation): string | null {
    let message: string | null = null
    // Falls back when the notifying user has since been deleted — the
    // server resolves notifier_user by id and can return null.
    const name =
      notification.notifier_user !== null
        ? this.userDisplayName(notification.notifier_user)
        : 'Usuario'

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
    }

    return message
  }

  setSnackBar(snack: Partial<SnackBarState> = {}): void {
    sessionStorage.setItem('snack', JSON.stringify(snack))
  }

  getSnackBar(): Partial<SnackBarState> | null {
    const stored = sessionStorage.getItem('snack')
    sessionStorage.removeItem('snack')
    return stored === null ? null : (JSON.parse(stored) as Partial<SnackBarState>)
  }

  checkFormValidity(form: HTMLFormElement): boolean {
    if (!form.checkValidity()) {
      form.reportValidity()
      return false
    }

    return true
  }

  animate(node: HTMLElement, animation: string, prefix = 'animate__'): Promise<string> {
    // We create a Promise and return it
    return new Promise((resolve) => {
      const animationName = `${prefix}${animation}`

      node.classList.add(`${prefix}animated`, animationName)

      // When the animation ends, we clean the classes and resolve the Promise
      function handleAnimationEnd(event: Event) {
        event.stopPropagation()
        node.classList.remove(`${prefix}animated`, animationName)
        resolve('Animation ended')
      }

      node.addEventListener('animationend', handleAnimationEnd, { once: true })
    })
  }

  shareLinks(title: string, url: string): Array<{ name: string; url: string; icon: string }> {
    const facebookBaseUrl = `https://facebook.com/sharer/sharer.php?u=${url}`
    const twitterBaseUrl = `https://twitter.com/intent/tweet/?text=${title}&url=${url}`
    const whatsappBaseUrl = `whatsapp://send?text=${title}%20${url}`
    const telegramBaseUrl = `https://t.me/share/url?url=${url}&text=${title}`

    return [
      {
        name: 'Facebook',
        url: facebookBaseUrl,
        icon: 'fab fa-facebook-f'
      },
      {
        name: 'Twitter',
        url: twitterBaseUrl,
        icon: 'fab fa-x-twitter'
      },
      {
        name: 'Whatsapp',
        url: whatsappBaseUrl,
        icon: 'fab fa-whatsapp'
      },
      {
        name: 'Telegram',
        url: telegramBaseUrl,
        icon: 'fab fa-telegram'
      },
      {
        name: 'copy',
        url: '#',
        icon: 'far fa-clone'
      }
    ]
  }

  socialIcon(): Record<string, string | undefined> {
    return {
      twitter: 'fab fa-x-twitter',
      threads: 'fab fa-threads',
      instagram: 'fab fa-instagram',
      facebook: 'fab fa-facebook-f',
      youtube: 'fab fa-youtube',
      telegram: 'fab fa-telegram'
    }
  }

  asset(url: string): string {
    return new URL(url, route('home')).toString()
  }

  karmaMedal(grade: string): string | null {
    let medal: string | null = null

    if ('C' == grade) {
      medal = 'deep-orange-accent-1'
    } else if ('B' == grade) {
      medal = 'blue-grey-lighten-3'
    } else if ('A' == grade) {
      medal = 'amber-accent-4'
    }

    return medal
  }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $helper: Helper
  }
}

export const helper = {
  install: (app: App) => {
    const instance = new Helper()
    app.config.globalProperties.$helper = instance
    app.provide(helperKey, instance)
  }
}
