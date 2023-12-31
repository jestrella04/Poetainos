import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import laravel from 'laravel-vite-plugin'
import Components from 'unplugin-vue-components/vite'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      refresh: true
    }),
    vue(),
    vuetify({ autoImport: true }),
    Components({
      dirs: ['resources/js/components/common'],
      extensions: ['vue']
    })
  ]
})
