import type { App } from 'vue'
import type { ComposerTranslation } from 'vue-i18n'
import { helperKey, type SnackBarState } from '../composables/keys'
import { useTypeGuards } from '../composables/useTypeGuards'
import { useAuth } from '../composables/useAuth'
import { useFormatting } from '../composables/useFormatting'
import { useSocialLinks } from '../composables/useSocialLinks'
import { useSnackbar } from '../composables/useSnackbar'
import {
  useNotificationMessage,
  type NotificationLike
} from '../composables/useNotificationMessage'
import { useFormValidation } from '../composables/useFormValidation'
import { useAnimation } from '../composables/useAnimation'
import type { UserLike } from '../types/models'

/**
 * Thin facade preserving the existing `$helper`/`helper.xyz()` call-site
 * surface used across the app's components, while the actual logic lives
 * in focused composables under resources/js/composables/. New code should
 * prefer importing those composables directly; this facade exists so
 * existing templates and scripts keep working unchanged.
 */
export class Helper {
  private typeGuards = useTypeGuards()
  private authApi = useAuth()
  private formatting = useFormatting()
  private socialLinks = useSocialLinks()
  private snackbar = useSnackbar()
  private notificationMsg = useNotificationMessage()
  private formValidation = useFormValidation()
  private animation = useAnimation()

  auth(): boolean {
    return this.authApi.auth()
  }

  authUser() {
    return this.authApi.authUser()
  }

  admin(): boolean {
    return this.authApi.admin()
  }

  canEdit(author: { username: string }): boolean {
    return this.authApi.canEdit(author)
  }

  isNil(obj: unknown): obj is null | undefined {
    return this.typeGuards.isNil(obj)
  }

  isNull(obj: unknown): obj is null {
    return this.typeGuards.isNull(obj)
  }

  isEmpty(obj: unknown): boolean {
    return this.typeGuards.isEmpty(obj)
  }

  strNullOrEmpty(str: string | null | undefined): boolean {
    return this.typeGuards.strNullOrEmpty(str)
  }

  storage(path: string): string {
    return this.formatting.storage(path)
  }

  userDisplayName(user: UserLike): string {
    return this.formatting.userDisplayName(user)
  }

  userInitials(user: UserLike): string {
    return this.formatting.userInitials(user)
  }

  readable(value: number): string {
    return this.formatting.readable(value)
  }

  toLocaleDate(date: string | number | Date): string {
    return this.formatting.toLocaleDate(date)
  }

  relativeDate(date: string | number | Date): string {
    return this.formatting.relativeDate(date)
  }

  excerpt(text: string): string {
    return this.formatting.excerpt(text)
  }

  cropUrl(url: string, max = 40): string {
    return this.formatting.cropUrl(url, max)
  }

  linkify(text: string): string {
    return this.formatting.linkify(text)
  }

  markdown(md: string): string {
    return this.formatting.markdown(md)
  }

  asset(url: string): string {
    return this.formatting.asset(url)
  }

  karmaMedal(grade: string): string | null {
    return this.formatting.karmaMedal(grade)
  }

  socialLink(user: string, network: string): string {
    return this.socialLinks.socialLink(user, network)
  }

  shareLinks(title: string, url: string): Array<{ name: string; url: string; icon: string }> {
    return this.socialLinks.shareLinks(title, url)
  }

  socialIcon(): Record<string, string | undefined> {
    return this.socialLinks.socialIcon()
  }

  notificationMessage(notification: NotificationLike, t: ComposerTranslation): string | null {
    return this.notificationMsg.notificationMessage(notification, t)
  }

  setSnackBar(snack: Partial<SnackBarState> = {}): void {
    this.snackbar.setSnackBar(snack)
  }

  getSnackBar(): Partial<SnackBarState> | null {
    return this.snackbar.getSnackBar()
  }

  checkFormValidity(form: HTMLFormElement): boolean {
    return this.formValidation.checkFormValidity(form)
  }

  animate(node: HTMLElement, animation: string, prefix = 'animate__'): Promise<string> {
    return this.animation.animate(node, animation, prefix)
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
