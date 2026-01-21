import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import laravel from 'laravel-vite-plugin'
import Components from 'unplugin-vue-components/vite'
import { VuetifyResolver } from 'unplugin-vue-components/resolvers'
import { VitePWA } from 'vite-plugin-pwa'
import { nodePolyfills } from 'vite-plugin-node-polyfills'
import path from 'path'

export default defineConfig({
  server: {
    host: '0.0.0.0',
    hmr: {
      host: 'localhost'
    }
  },
  resolve: {
    alias: {
      'ziggy-js': path.resolve('/vendor/tightenco/ziggy')
    }
  },
  build: {
    sourcemap: true,
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor-vue': ['vue', '@vue/runtime-dom', '@vue/runtime-core'],
          'vendor-vuetify': ['vuetify'],
          'vendor-inertia': ['@inertiajs/vue3']
        }
      }
    }
  },
  plugins: [
    nodePolyfills(),
    laravel({
      input: ['resources/js/app.js'],
      ssr: ['resources/js/ssr.js'],
      refresh: true
    }),
    vue(),
    vuetify(),
    Components({
      dirs: ['resources/js/components/common'],
      resolvers: [VuetifyResolver()],
      include: [/\.vue$/, /\.vue\?vue/, /\.vue\.[tj]sx?\?vue/, /\.md$/],
    }),
    VitePWA({
      scope: '/',
      base: '/',
      srcDir: 'resources/js',
      outDir: 'public',
      filename: 'worker.js',
      strategies: 'injectManifest',
      injectRegister: false,
      includeManifestIcons: false,
      manifest: false,
      registerType: 'autoUpdate',
      devOptions: {
        enabled: true,
        type: 'module',
        suppressWarnings: true
      },
      workbox: {
        cleanupOutdatedCaches: true
      },
      injectManifest: {
        maximumFileSizeToCacheInBytes: 3000000
      }
    })
  ]
})
