import type { AxiosError } from 'axios'
import type { LaravelValidationErrors } from '@/types/http'

interface ErrorBody {
  errors?: LaravelValidationErrors
}

/**
 * Reads what a failed form request says about individual fields.
 */
export function useFormErrors() {
  /**
   * The per-field messages of a validation failure. Empty for anything else
   * (a throttled request, a server error, no connection…), which carries none.
   */
  function validationErrors(error: unknown): LaravelValidationErrors {
    return (error as AxiosError<ErrorBody>).response?.data?.errors ?? {}
  }

  return { validationErrors }
}
