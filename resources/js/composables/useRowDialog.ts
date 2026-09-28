import { ref, type Ref } from 'vue'

/**
 * A dialog about one row of a table (editing or deleting it), or about a new
 * one when opened without a row.
 */
export function useRowDialog<T>() {
  const row = ref(null) as Ref<T | null>
  const isOpen = ref(false)

  function open(target: T | null = null): void {
    row.value = target
    isOpen.value = true
  }

  return { row, isOpen, open }
}
