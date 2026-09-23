/// <reference types="vite/client" />
/// <reference types="vite-plugin-pwa/vue" />

interface ImportMetaEnv {
  readonly VITE_VAPID_PUBLIC_KEY?: string
  readonly VITE_PUSHER_APP_KEY?: string
  readonly VITE_PUSHER_APP_CLUSTER?: string
  readonly VITE_PUSHER_APP_FORCETLS?: string
  readonly VITE_PUSHER_HOST?: string
  readonly VITE_PUSHER_PORT?: string
}

// The Get Installed Related Apps API (PoFooter.vue) isn't in TS's bundled
// DOM lib yet.
interface RelatedApplication {
  id?: string
  platform: string
  url?: string
}

interface Navigator {
  getInstalledRelatedApps?: () => Promise<RelatedApplication[]>
}

declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, unknown>
  export default component
}
