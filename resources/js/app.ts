import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue, route } from 'ziggy-js'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { vuetify, themeStylesheetHead } from './plugins/vuetify'
import { i18n } from './plugins/i18n'
import { push } from './plugins/push'
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

library.add(far)
library.add(fas)
library.add(fab)

void createInertiaApp({
  pages: './components',
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
