import { createInertiaApp } from '@inertiajs/vue3'
import type { DefineComponent } from 'vue'
import { ZiggyVue, route } from 'ziggy-js'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { vuetify, themeStylesheetHead } from './plugins/vuetify'
import { i18n } from './plugins/i18n'
import { push } from './plugins/push'
import './plugins/fontawesome'
import '@fontsource/eb-garamond/400.css'
import '@fontsource/eb-garamond/500.css'
import '@fontsource/eb-garamond/600.css'
import '@fontsource/eb-garamond/400-italic.css'
import '@fontsource/karla/400.css'
import '@fontsource/karla/500.css'
import '@fontsource/karla/600.css'
import '@fontsource/karla/700.css'
import '../css/app.css'
import PoLayoutMain from './components/layouts/PoLayoutMain.vue'

// `common/` and `layouts/` hold building blocks that pages import statically, never
// pages themselves; globbing them too would make each one a useless dynamic import.
const pages = import.meta.glob<{ default: DefineComponent }>([
  './components/**/*.vue',
  '!./components/common/**',
  '!./components/layouts/**'
])

void createInertiaApp({
  resolve: async (name) => {
    const page = pages[`./components/${name}.vue`]

    if (page === undefined) {
      throw new Error(`Page not found: ${name}`)
    }

    return (await page()).default
  },
  layout: () => PoLayoutMain,
  serverHead: themeStylesheetHead,
  progress: {
    delay: 0,
    showSpinner: true
  },
  withApp(app, { page, ssr }) {
    if (ssr === true) {
      // Composables call the global `route()`, which the browser gets from the
      // `@routes` script; the SSR process has no such script.
      Object.assign(globalThis, { route, Ziggy: page.props.ziggy })
    }

    app
      .use(vuetify)
      .use(i18n)
      .use(ZiggyVue, page.props.ziggy)
      .use(push)
      .component('font-awesome-icon', FontAwesomeIcon)
  }
})
