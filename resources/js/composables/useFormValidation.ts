/**
 * Native HTML5 form validation. v-form renders with `novalidate`, so the
 * browser's own checks (required, pattern, length…) only run when asked.
 */
export function useFormValidation() {
  /**
   * Whether the form a submit event came from passes the browser's checks,
   * pointing the user at the first field that fails.
   */
  function isSubmittedFormValid(event: Event): boolean {
    const form = event.target

    if (!(form instanceof HTMLFormElement) || form.checkValidity()) {
      return true
    }

    form.reportValidity()

    return false
  }

  return { isSubmittedFormValid }
}
