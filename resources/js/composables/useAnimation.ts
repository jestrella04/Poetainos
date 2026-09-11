/**
 * animate.css class toggling, resolved once the animation finishes.
 */
export function useAnimation() {
  function animate(node: HTMLElement, animation: string, prefix = 'animate__'): Promise<string> {
    return new Promise((resolve) => {
      const animationName = `${prefix}${animation}`

      node.classList.add(`${prefix}animated`, animationName)

      function handleAnimationEnd(event: Event) {
        event.stopPropagation()
        node.classList.remove(`${prefix}animated`, animationName)
        resolve('Animation ended')
      }

      node.addEventListener('animationend', handleAnimationEnd, { once: true })
    })
  }

  return { animate }
}
