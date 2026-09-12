import { ref, type Ref } from 'vue'
import axios from 'axios'

export interface FormSubmitConfig<TErrors> {
  /** CSS selector for the `<v-form>` whose `action` attribute is posted to. */
  formSelector: string
  /** Request body to post to the form's `action`. */
  payload: Record<string, unknown>
  /** Called after the post (and optional `preSubmit`) succeeds. */
  onSuccess: () => void
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
 * Shared confirm-and-submit-via-form pattern used by the delete/block/report
 * modals: find the `<v-form>` by selector, POST its payload, and track
 * `isPosting`/`errors` around the request.
 */
export function useFormSubmit<TErrors>(initialErrors: TErrors) {
  const isPosting = ref(false)
  const errors = ref(initialErrors) as Ref<TErrors>

  async function submitForm(config: FormSubmitConfig<TErrors>): Promise<void> {
    const form = document.querySelector<HTMLFormElement>(config.formSelector)

    if (form === null) {
      return
    }

    isPosting.value = true
    errors.value = initialErrors

    try {
      if (config.preSubmit !== undefined) {
        await config.preSubmit()
      }

      await axios.post(form.action, config.payload)
      config.onSuccess()
    } catch (error) {
      errors.value = config.onError(error)
    } finally {
      isPosting.value = false
    }
  }

  return { isPosting, errors, submitForm }
}
