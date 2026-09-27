import { isEmpty as lodashIsEmpty } from 'lodash-es'

/**
 * Emptiness checks shared across components and other composables.
 */
export function useTypeGuards() {
  /**
   * Whether a value holds nothing: null, undefined, or an empty string, array, object, map or set.
   */
  function isEmpty(value: unknown): boolean {
    return lodashIsEmpty(value)
  }

  /**
   * Whether a string is missing or holds nothing but whitespace.
   */
  function isBlank(text: string | null | undefined): boolean {
    return text === null || text === undefined || text.trim() === ''
  }

  return { isEmpty, isBlank }
}
