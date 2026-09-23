import { ref, type Ref } from 'vue'
import axios from 'axios'
import { useFormValidation } from '@/composables/useFormValidation'

// The submit button stays disabled a little after a request settles so a double click can't repost.
const POSTING_COOLDOWN_MS = 1000

export interface FormSubmitConfig<TErrors, TResult> {
  /** CSS selector for the `<v-form>` whose native validity is checked and whose `action` is posted to. */
  formSelector: string
  /** Post here instead of the form's `action` (for forms that talk to several endpoints). */
  url?: string
  /** Request body to post. */
  payload: Record<string, unknown>
  /** Send the body as multipart form data (file uploads). */
  multipart?: boolean
  /** Check the form's native validity before posting. On by default. */
  validate?: boolean
  /** Keep `isPosting` on for a moment after the request settles. */
  cooldown?: boolean
  /** Called after the post (and optional `preSubmit`) succeeds, with the response body. */
  onSuccess: (data: TResult) => void
  /** Maps a caught error to the value stored in `errors`. */
  onError: (error: unknown) => TErrors
  /**
   * An optional async step run before the main post (e.g. a password
   * confirmation request). Composes on top instead of being special-cased:
   * its rejection is caught the same way as the main post's.
   */
  preSubmit?: () => Promise<void>
}

/**
 * Shared validate-and-submit-via-form pattern used by the forms and the
 * delete/block/report modals: find the `<v-form>` by selector, check its
 * validity, POST its payload, and track `isPosting`/`errors` around the request.
 */
export function useFormSubmit<TErrors>(initialErrors: TErrors) {
  const { checkFormValidity } = useFormValidation()
  const isPosting = ref(false)
  const errors = ref(initialErrors) as Ref<TErrors>

  async function submitForm<TResult = unknown>(
    config: FormSubmitConfig<TErrors, TResult>
  ): Promise<void> {
    const form = document.querySelector<HTMLFormElement>(config.formSelector)

    if (form === null) {
      return
    }

    errors.value = initialErrors

    if (config.validate !== false && checkFormValidity(form) === false) {
      return
    }

    isPosting.value = true

    try {
      if (config.preSubmit !== undefined) {
        await config.preSubmit()
      }

      const url = config.url ?? form.action
      const response =
        config.multipart === true
          ? await axios.postForm<TResult>(url, config.payload)
          : await axios.post<TResult>(url, config.payload)

      config.onSuccess(response.data)
    } catch (error) {
      errors.value = config.onError(error)
    } finally {
      if (config.cooldown === true) {
        setTimeout(() => {
          isPosting.value = false
        }, POSTING_COOLDOWN_MS)
      } else {
        isPosting.value = false
      }
    }
  }

  return { isPosting, errors, submitForm }
}
