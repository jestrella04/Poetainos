import * as _ from 'lodash-es'

/**
 * Generic null/empty checks shared across components and other
 * composables.
 */
export function useTypeGuards() {
  function isNil(obj: unknown): obj is null | undefined {
    return _.isNil(obj)
  }

  function isNull(obj: unknown): obj is null {
    return _.isNull(obj)
  }

  function isEmpty(obj: unknown): boolean {
    return _.isEmpty(obj)
  }

  function strNullOrEmpty(str: string | null | undefined): boolean {
    return _.isNil(str) || '' === str.trim()
  }

  return { isNil, isNull, isEmpty, strNullOrEmpty }
}
