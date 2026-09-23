import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(import.meta.dirname, 'resources/js')
    }
  },
  test: {
    environment: 'jsdom',
    include: ['resources/js/**/__tests__/**/*.{test,spec}.ts']
  }
})
