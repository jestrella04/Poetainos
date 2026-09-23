import { isEmpty as lodashIsEmpty, isNil as lodashIsNil, isNull as lodashIsNull } from 'lodash-es'

/**
 * Generic null/empty checks shared across components and other
 * composables.
 */
export function useTypeGuards() {
  function isNil(obj: unknown): obj is null | undefined {
    return lodashIsNil(obj)
  }

  function isNull(obj: unknown): obj is null {
    return lodashIsNull(obj)
  }

  function isEmpty(obj: unknown): boolean {
    return lodashIsEmpty(obj)
  }

  function strNullOrEmpty(str: string | null | undefined): boolean {
    return lodashIsNil(str) || '' === str.trim()
  }

  return { isNil, isNull, isEmpty, strNullOrEmpty }
}
