/**
 * Native HTML5 form validation helpers.
 */
export function useFormValidation() {
  function checkFormValidity(form: HTMLFormElement): boolean {
    if (!form.checkValidity()) {
      form.reportValidity()
      return false
    }

    return true
  }

  return { checkFormValidity }
}
