import { describe, expect, it, vi, afterEach } from 'vitest'
import { createSSRApp, defineComponent, h, nextTick } from 'vue'
import { renderToString } from 'vue/server-renderer'
import { mount } from '@vue/test-utils'
import { useSystemTheme } from '../useSystemTheme'

const changeTheme = vi.fn()

vi.mock('vuetify', () => ({
  useTheme: () => ({ change: changeTheme })
}))

const Host = defineComponent({
  setup() {
    const { revealStyle } = useSystemTheme()
    return () => h('div', { style: revealStyle.value })
  }
})

afterEach(() => {
  vi.clearAllMocks()
})

describe('useSystemTheme', () => {
  it("hands theme selection to Vuetify's system theme once mounted", () => {
    // When
    mount(Host)

    // Then
    expect(changeTheme).toHaveBeenCalledExactlyOnceWith('system')
  })

  it('leaves the theme untouched during server rendering, where onMounted never runs', async () => {
    // When
    await renderToString(createSSRApp(Host))

    // Then
    expect(changeTheme).not.toHaveBeenCalled()
  })

  it('renders the app hidden on the server so the light markup is never painted', async () => {
    // When
    const html = await renderToString(createSSRApp(Host))

    // Then
    expect(html).toContain('opacity:0')
  })

  it('fades the app in once the theme has been applied', async () => {
    // When
    const wrapper = mount(Host)
    await nextTick()

    // Then
    expect(wrapper.attributes('style')).toContain('opacity: 1')
    expect(wrapper.attributes('style')).toContain('transition: opacity 0.15s')
  })
})
