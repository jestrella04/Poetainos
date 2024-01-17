// plugins/route.js
export const route = {
  install: (app) => {
    app.config.globalProperties.$route = window.route
  }
}
