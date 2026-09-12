/**
 * Shares via the browser's native Web Share API when available, falling
 * back to the caller-provided handler (typically opening `PoSharer`) when
 * it isn't.
 */
export function useNativeShare() {
  function share(title: string, url: string, onUnsupported: () => void): void {
    if (navigator.share !== undefined) {
      void navigator.share({ title, url })
    } else {
      onUnsupported()
    }
  }

  return { share }
}
