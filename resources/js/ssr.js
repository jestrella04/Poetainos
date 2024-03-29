import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { renderToString } from '@vue/server-renderer'
import { createSSRApp, h } from 'vue'
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

createServer((page) => {
  createInertiaApp({
    page,
    render: renderToString,
    resolve: (name) => {
      const pages = import.meta.glob('./components/**/*.vue', {
        eager: true
      })
      let component = pages[`./components/${name}.vue`]
      component.default.layout = component.default.layout || PoLayoutMain
      return component
    },
    setup({ App, props, plugin }) {
      const app = createSSRApp({
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
      //.mount(el)
    }
  })
})
