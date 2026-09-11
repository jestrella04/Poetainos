import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import laravel from 'laravel-vite-plugin'
import Components from 'unplugin-vue-components/vite'
import { VuetifyResolver } from 'unplugin-vue-components/resolvers'
import { VitePWA } from 'vite-plugin-pwa'
import { nodePolyfills } from 'vite-plugin-node-polyfills'
import path from 'path'

const manualChunkGroups = {
  'vendor-vue': ['vue', '@vue/runtime-dom', '@vue/runtime-core'],
  'vendor-vuetify': ['vuetify'],
  'vendor-inertia': ['@inertiajs/vue3'],
  'vendor-fontawesome': [
    '@fortawesome/fontawesome-svg-core',
    '@fortawesome/vue-fontawesome',
    '@fortawesome/free-solid-svg-icons',
    '@fortawesome/free-regular-svg-icons',
    '@fortawesome/free-brands-svg-icons'
  ],
  'vendor-realtime': ['laravel-echo', 'pusher-js'],
  'vendor-i18n': ['vue-i18n'],
  'vendor-vueuse': ['@vueuse/core'],
  'vendor-utils': ['date-fns', 'lodash-es', 'millify', 'crop-url', 'linkifyjs'],
  'vendor-markdown': ['markdown-it']
}

function manualChunks(id) {
  if (!id.includes('node_modules')) {
    return
  }

  for (const [chunk, packages] of Object.entries(manualChunkGroups)) {
    if (packages.some((pkg) => id.includes(`/node_modules/${pkg}/`))) {
      return chunk
    }
  }
}

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');

  return {
    server: {
      cors: true,
      host: '0.0.0.0',
      hmr: {
        host: env.VITE_HMR_HOST || 'localhost',
      },
    },
  resolve: {
    alias: {
      'ziggy-js': path.resolve('/vendor/tightenco/ziggy'),
      '@': path.resolve(import.meta.dirname, 'resources/js')
    }
  },
  optimizeDeps: {
    include: ['vuetify']
  },
  ssr: {
    // avoid bundling vuetify for server build, helps with ESM resolution
    external: ['vuetify']
  },
  build: {
    sourcemap: true,
    rollupOptions: {
      output: {
        manualChunks
      }
    }
  },
  plugins: [
    nodePolyfills(),
    laravel({
      input: ['resources/js/app.ts'],
      ssr: ['resources/js/ssr.js'],
      refresh: true
    }),
    vue(),
    vuetify({ autoImport: true }),
    Components({
      dirs: ['resources/js/components/common'],
      resolvers: [VuetifyResolver()],
      include: [/\.vue$/, /\.vue\?vue/, /\.vue\.[tj]sx?\?vue/, /\.md$/],
      dts: 'resources/js/components.d.ts'
    }),
    VitePWA({
      scope: '/',
      base: '/',
      srcDir: 'resources/js',
      outDir: 'public',
      filename: 'worker.ts',
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
}});
