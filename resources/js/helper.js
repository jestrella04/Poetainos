import _ from 'lodash'
import millify from 'millify'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import crop from 'crop-url'

const page = computed(() => usePage())

const helper = class {
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
}

export default helper
