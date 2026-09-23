// The ziggy-js package this project resolves against (see the `ziggy-js`
// alias in vite.config.js) ships no types. `route()` reaches the app two
// ways: the `@routes` Blade directive in resources/views/app.blade.php
// prints it as a genuine `window.route` global (usable from plain modules
// like helper.ts), and the ZiggyVue plugin separately registers it as a
// Vue global property (`app.config.globalProperties.route`) — Vue's
// template compiler resolves bare template identifiers against the
// component instance, not arbitrary globals, so templates need the second
// form even though it's the same underlying function at runtime.
export {}

type RouteFn = (
  name: string,
  params?: string | number | Array<string | number> | Record<string, unknown>,
  absolute?: boolean
) => string

declare global {
  const route: RouteFn
}

declare module 'vue' {
  interface ComponentCustomProperties {
    route: RouteFn
  }
}
