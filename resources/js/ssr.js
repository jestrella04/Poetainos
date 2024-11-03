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
//import { push } from './plugins/push'
import 'animate.css'
import PoLayoutMain from './components/layouts/PoLayoutMain.vue'
import { ZiggyVue } from 'ziggy-js'

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
      let page = pages[`./components/${name}.vue`]
      page.default.layout = page.default.layout || PoLayoutMain
      return page
    },
    setup({ App, props, plugin }) {
      const Ziggy = {
        // Pull the Ziggy config off of the props.
        ...props.initialPage.props.ziggy,
        // Build the location, since there is
        // no window.location in Node.
        location: new URL(props.initialPage.props.ziggy.url)
      }

      const app = createSSRApp({
        render: () => h(App, props)
      })

      app
        .use(plugin)
        .use(vuetify)
        .use(i18n)
        .use(helper)
        .use(ZiggyVue)
        //.use(push)
        .component('font-awesome-icon', FontAwesomeIcon)
        .mixin({
          methods: {
            route: (name, params, absolute, config = Ziggy) =>
              ZiggyVue(name, params, absolute, config)
          }
        })
      //.mount(el)
    }
  })
})
