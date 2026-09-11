import { createApp, h } from 'vue'
import type { DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from 'ziggy-js'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { vuetify } from './plugins/vuetify'
import { i18n } from './plugins/i18n'
import { helper } from './plugins/helper'
import { push } from './plugins/push'
import 'animate.css'
import PoLayoutMain from './components/layouts/PoLayoutMain.vue'

library.add(far)
library.add(fas)
library.add(fab)

// Inertia's page components carry an optional `layout` property, a
// convention layered on top of Vue's DefineComponent, not part of it.
type InertiaPageComponent = DefineComponent & { layout?: unknown }

void createInertiaApp({
  progress: {
    delay: 0,
    color: '#B39DDB',
    showSpinner: true
  },
  resolve: async (name) => {
    const page = await resolvePageComponent<{ default: InertiaPageComponent }>(
      `./components/${name}.vue`,
      import.meta.glob<{ default: InertiaPageComponent }>('./components/**/*.vue')
    )
    page.default.layout = page.default.layout || PoLayoutMain
    return page.default
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
      .use(ZiggyVue)
      .use(push)
      .component('font-awesome-icon', FontAwesomeIcon)
      .mount(el)
  }
})
