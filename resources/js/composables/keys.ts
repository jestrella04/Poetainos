import type { InjectionKey, Ref } from 'vue'
import type { Helper } from '../plugins/helper'
import type { Push } from '../plugins/push'
import type { User, Writing } from '../types/models'

// Typed provide()/inject() keys, one per bare string key currently used
// across resources/js/components. Using a shared symbol per key guarantees
// the provider and every consumer agree on the value's type, instead of
// each inject() call guessing independently.
//
// `authorKey`/`karmaKey` are intentionally absent: `inject('author')` and
// `inject('karma')` (in PoKarmaInspire.vue / PoWritingKarma.vue) have no
// matching provide() anywhere in the codebase and neither component is
// referenced by any other component — dead code to remove in the Batch D
// conversion, not injections to type.

export const complainerKey: InjectionKey<Ref<boolean>> = Symbol('complainer')
export const blockerKey: InjectionKey<Ref<boolean>> = Symbol('blocker')
export const sharerKey: InjectionKey<Ref<boolean>> = Symbol('sharer')
export const isDeleteKey: InjectionKey<Ref<boolean>> = Symbol('isDelete')
export const replyBoxKey: InjectionKey<Ref<number>> = Symbol('replyBox')
export const loadingCommentsKey: InjectionKey<Ref<boolean>> = Symbol('loadingComments')
export const loginModalKey: InjectionKey<Ref<boolean>> = Symbol('loginModal')
export const mobileSiteMenuKey: InjectionKey<Ref<boolean>> = Symbol('mobileSiteMenu')
export const mobileUserMenuKey: InjectionKey<Ref<boolean>> = Symbol('mobileUserMenu')
export const forceSnackBarKey: InjectionKey<Ref<boolean>> = Symbol('forceSnackBar')
export const unreadCountKey: InjectionKey<Ref<number>> = Symbol('unreadCount')
export const helperKey: InjectionKey<Helper> = Symbol('helper')
export const pushKey: InjectionKey<Push> = Symbol('push')

export interface SnackBarState {
  active: boolean
  avatar: string
  color: string
  timeout: number
  message: string
}

export const snackBarKey: InjectionKey<SnackBarState> = Symbol('snackBar')

// Shared by PoLogin.vue, PoUsersForm.vue and PoWritingsForm.vue, each of
// which embeds <po-agreement> — the only actual consumer of this key — so
// every form's data must carry these two fields alongside its own.
export interface AgreementFormData {
  serviceAgreement: boolean
  privacyAgreement: boolean
  [key: string]: unknown
}

export const formDataKey: InjectionKey<AgreementFormData> = Symbol('formData')

export const userKey: InjectionKey<User> = Symbol('user')
export const writingKey: InjectionKey<Writing> = Symbol('writing')
