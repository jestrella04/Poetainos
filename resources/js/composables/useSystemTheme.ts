import { computed, onMounted, ref } from 'vue'
import type { ComputedRef, StyleValue } from 'vue'
import { useTheme } from 'vuetify'

/**
 * Follows the OS color scheme once mounted. Deferred so the client's first
 * render matches the server-rendered (light) markup and hydrates cleanly.
 *
 * `revealStyle` keeps the app invisible until that switch has happened, so a
 * dark-scheme user never sees the light markup flash before the theme applies.
 */
export function useSystemTheme(): { revealStyle: ComputedRef<StyleValue> } {
  const theme = useTheme()
  const isReady = ref(false)
  const revealStyle = computed<StyleValue>(() =>
    isReady.value ? { opacity: 1, transition: 'opacity 0.15s' } : { opacity: 0 }
  )

  onMounted(() => {
    void theme.change('system')
    isReady.value = true
  })

  return { revealStyle }
}
