// plugins/helper.js

import _ from 'lodash'
import millify from 'millify'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import crop from 'crop-url'
import linkifyHtml from 'linkify-html'
import 'linkify-plugin-mention'
import MarkdownIt from 'markdown-it'
import { intlFormatDistance } from 'date-fns'

const page = computed(() => usePage())

const Helper = class {
  constructor() {}

  auth() {
    const auth = page.value.props.auth
    return !this.isNull(auth.user) && !this.strNullOrEmpty(auth.user.username)
  }

  authUser() {
    return page.value.props.auth.user
  }

  admin() {
    const auth = page.value.props.auth
    return auth.admin
  }

  storage(path) {
    return `/storage/${path}`
  }

  isNil(obj) {
    return _.isNil(obj)
  }

  isNull(obj) {
    return _.isNull(obj)
  }

  isEmpty(obj) {
    return _.isEmpty(obj)
  }

  strNullOrEmpty(str) {
    return _.isNil(str) || '' === str.trim()
  }

  userDisplayName(user) {
    if (!_.isNil(user.name) && '' !== user.name) {
      return user.name
    }

    return user.username
  }

  userInitials(user) {
    if (!_.isNil(user.name) && !_.isNil(user.last_name)) {
      return _.toUpper(`${user.name.substring(0, 1)}${user.last_name.substring(0, 1)}`)
    }

    return _.toUpper(user.username.substring(0, 1))
  }

  readable(str) {
    return millify(str)
  }

  toLocaleDate(date) {
    return new Date(date).toLocaleDateString()
  }

  relativeDate(date) {
    return intlFormatDistance(new Date(date), new Date(), { locale: 'es' })
  }

  excerpt(text) {
    const len = text.length

    if (len < 400) {
      return text
    }

    return `${text.substring(0, 400)}...`
  }

  cropUrl(url, max = 40) {
    return crop(url, max)
  }

  linkify(text) {
    const options = {
      formatHref: {
        mention: (href) => `${window.route('users.index')}${href}`
      }
    }

    return linkifyHtml(text, options)
  }

  socialLink(user, network) {
    let url = ''

    switch (network) {
      case 'twitter':
        url = `https://twitter.com/${user}`
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

  markdown(md) {
    return MarkdownIt().render(md)
  }

  notificationMessage(notification, t) {
    let message = null

    switch (notification.type) {
      case 'App\\Notifications\\WritingCommented':
        message = t('comments.user-added', {
          name: this.userDisplayName(notification.notifier_user)
        })
        break

      case 'App\\Notifications\\WritingCommentMentioned':
      case 'App\\Notifications\\WritingReplyMentioned':
        message = t('comments.user-mentioned', {
          name: this.userDisplayName(notification.notifier_user)
        })
        break

      case 'App\\Notifications\\WritingFeatured':
        message = t('writings.writing-awarded')
        break

      case 'App\\Notifications\\WritingLiked':
        message = t('writings.user-liked', {
          name: this.userDisplayName(notification.notifier_user)
        })
        break

      case 'App\\Notifications\\WritingReplied':
        message = t('comments.user-replied', {
          name: this.userDisplayName(notification.notifier_user)
        })
        break

      case 'App\\Notifications\\WritingShelved':
        message = t('writings.user-shelved', {
          name: this.userDisplayName(notification.notifier_user)
        })
        break

      case 'App\\Notifications\\CommentLiked':
        message = t('comments.user-liked', {
          name: this.userDisplayName(notification.notifier_user)
        })
        break
    }

    return message
  }

  setSnackBar(snack = {}) {
    sessionStorage.setItem('snack', JSON.stringify(snack))
  }

  getSnackBar() {
    const snack = JSON.parse(sessionStorage.getItem('snack'))
    sessionStorage.removeItem('snack')
    return snack
  }
}

export const helper = {
  install: (app) => {
    const helper = new Helper()
    app.config.globalProperties.$helper = helper
    app.provide('helper', helper)
  }
}
