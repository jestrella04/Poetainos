import type { InjectionKey, Ref } from 'vue'
import type { Push } from '../plugins/push'
import type { User, Writing } from '../types/models'

// Typed provide()/inject() keys: a shared symbol per key guarantees the
// provider and every consumer agree on the value's type.

export const complainerKey: InjectionKey<Ref<boolean>> = Symbol('complainer')
export const blockerKey: InjectionKey<Ref<boolean>> = Symbol('blocker')
export const sharerKey: InjectionKey<Ref<boolean>> = Symbol('sharer')
export const isDeleteKey: InjectionKey<Ref<boolean>> = Symbol('isDelete')
export const replyBoxKey: InjectionKey<Ref<number>> = Symbol('replyBox')
export const loadingCommentsKey: InjectionKey<Ref<boolean>> = Symbol('loadingComments')
export const loginModalKey: InjectionKey<Ref<boolean>> = Symbol('loginModal')
export const mobileSiteMenuKey: InjectionKey<Ref<boolean>> = Symbol('mobileSiteMenu')
export const mobileUserMenuKey: InjectionKey<Ref<boolean>> = Symbol('mobileUserMenu')
export const unreadCountKey: InjectionKey<Ref<number>> = Symbol('unreadCount')
export const pushKey: InjectionKey<Push> = Symbol('push')

export interface SnackBarState {
  active: boolean
  avatar: string
  color: string
  timeout: number
  message: string
}

export const snackBarKey: InjectionKey<SnackBarState> = Symbol('snackBar')

export const userKey: InjectionKey<User> = Symbol('user')
export const writingKey: InjectionKey<Writing> = Symbol('writing')
