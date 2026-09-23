import { inject } from 'vue'
import type { InjectionKey } from 'vue'

// Many components consume a provide()/inject() value that's always set up
// by an ancestor (PoLayoutMain, a dropdown's own root, etc.) — vue's plain
// `inject()` types every call as possibly undefined regardless, since it
// can't see the component tree. This asserts the invariant with a clear
// runtime error instead of a silent "Cannot read properties of undefined"
// if it's ever violated.
export function injectStrict<T>(key: InjectionKey<T>): T {
  const value = inject(key)

  if (value === undefined) {
    throw new Error(`Could not resolve ${key.toString()} injection`)
  }

  return value
}
