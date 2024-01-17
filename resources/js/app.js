import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { vuetify } from './plugins/vuetify'
import { i18n } from './plugins/i18n'
import { helper } from './plugins/helper'
import { route } from './plugins/route'
import { push } from './plugins/push'
import 'animate.css'
import PoLayoutMain from './components/layouts/PoLayoutMain.vue'

library.add(far)
library.add(fas)
library.add(fab)

createInertiaApp({
  progress: { color: '#673AB7' },
  resolve: (name) => {
    const pages = import.meta.glob('./components/**/*.vue', {
      eager: true
    })
    let page = pages[`./components/${name}.vue`]
    page.default.layout = page.default.layout || PoLayoutMain
    return page
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({
      render: () => h(App, props)
    })

    app
      .use(plugin)
      .use(vuetify)
      .use(i18n)
      .use(helper)
      .use(route)
      .use(push)
      .component('font-awesome-icon', FontAwesomeIcon)
      .mount(el)
  }
})
