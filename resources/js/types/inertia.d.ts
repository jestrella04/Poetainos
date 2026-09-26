// Shared Inertia page props, sourced from
// app/Http/Middleware/HandleInertiaRequests.php's share() method. Only the
// props shared on *every* page live here; page-specific props (set by an
// individual controller's Inertia::render(...)) stay typed locally where
// they're consumed, e.g. `usePage<{ total: number }>()`.

interface SharedAuthUser {
  id: number
  username: string
  name: string
  avatar: string | null
}

interface SharedSiteLink {
  value: string
  icon?: string
}

interface SharedSiteConfig {
  name: string
  slogan: string
  image: string
  // Admin-editable JSON with no enforced schema on the PHP side; typed
  // loosely to match, rather than asserting a shape nothing guarantees.
  pagination: number
  social: Record<string, SharedSiteLink>
  stores: Record<string, SharedSiteLink>
}

interface SharedPageProps {
  auth: {
    user: SharedAuthUser | null
    admin: boolean | null
    notifications: number
    liked: {
      writings: number[]
      comments: number[]
    }
    shelved: number[]
  }
  route: {
    name: string | null
  }
  site: SharedSiteConfig
  flash: {
    message: string | null
  }
  // Consumed internally by the ZiggyVue plugin; app code only reads the
  // absolute base URL, so the rest is left unstructured rather than guessed.
  ziggy: { url: string } & Record<string, unknown>
}

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    sharedPageProps: SharedPageProps
  }
}

// Convenience for typing a page-specific `usePage<T>()` call — Inertia's
// `PageProps` constraint requires a string index signature, which this adds
// so call sites don't have to repeat `[key: string]: unknown` themselves.
export type InertiaPageProps<T> = T & Record<string, unknown>

export {}
