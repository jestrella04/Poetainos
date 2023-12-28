import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import PoLayout from './components/common/PoLayout.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { vuetify } from './config/vuetify'
import { i18n } from './config/i18n'
import helper from './helper'

library.add(fas)
library.add(fab)

createInertiaApp({
  progress: {
    delay: 250,
    color: '#673AB7',
    includeCSS: true,
    showSpinner: true
  },
  resolve: (name) => {
    const pages = import.meta.glob('./components/**/*.vue', {
      eager: true
    })
    let page = pages[`./components/${name}.vue`]
    page.default.layout = page.default.layout || PoLayout
    return page
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({
      render: () => h(App, props)
    })

    app.config.globalProperties.$helper = new helper()
    app.config.globalProperties.$route = window.route
    app.use(plugin).use(vuetify).use(i18n).component('font-awesome-icon', FontAwesomeIcon).mount(el)
  }
})
