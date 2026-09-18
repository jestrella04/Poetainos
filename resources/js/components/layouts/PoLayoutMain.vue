<script setup lang="ts">
import { computed, ref, reactive, onMounted, onUpdated, watch, provide } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from 'vuetify'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import '@khmyznikov/pwa-install'
import {
  forceSnackBarKey,
  loginModalKey,
  mobileSiteMenuKey,
  mobileUserMenuKey,
  snackBarKey,
  unreadCountKey
} from '@/composables/keys'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useSnackbar } from '@/composables/useSnackbar'
import { useStaticPages } from '@/composables/useStaticPages'

const page = computed(() => usePage())
const { auth, authUser, admin } = useAuth()
const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { getSnackBar } = useSnackbar()
const { faqPath, aboutPath, termsPath, privacyPath } = useStaticPages()
const theme = useTheme()
const desktopSiteMenu = ref(false)
const mobileUserMenu = ref(false)
const mobileSiteMenu = ref(false)
const forceSnackBar = ref(false)
const unreadCount = ref(page.value.props.auth.notifications)
const loginModal = ref(false)
const installComponent = document.createElement('pwa-install')
const snackBar = reactive({
  active: false,
  avatar: '/images/logo.svg',
  color: 'info',
  timeout: 6000,
  message: ''
})
const echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  wsHost: import.meta.env.VITE_PUSHER_HOST,
  wsPort: import.meta.env.VITE_PUSHER_PORT ? Number(import.meta.env.VITE_PUSHER_PORT) : undefined,
  wssPort: import.meta.env.VITE_PUSHER_PORT ? Number(import.meta.env.VITE_PUSHER_PORT) : undefined,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: import.meta.env.VITE_PUSHER_APP_FORCETLS === 'true',
  disableStats: true,
  // PusherConnector connects synchronously during Echo's constructor, so
  // Pusher must be supplied here rather than assigned on the instance
  // afterwards (the connection attempt would already have failed).
  Pusher
})
document.body.appendChild(installComponent)
void (window.matchMedia('(prefers-color-scheme: dark)').matches
  ? theme.change('dark')
  : theme.change('light'))

provide(snackBarKey, snackBar)
provide(forceSnackBarKey, forceSnackBar)
provide(mobileSiteMenuKey, mobileSiteMenu)
provide(mobileUserMenuKey, mobileUserMenu)
provide(unreadCountKey, unreadCount)
provide(loginModalKey, loginModal)

onMounted(() => {
  getFlashMessages()

  if (auth() && 'setAppBadge' in navigator) {
    void navigator.setAppBadge(unreadCount.value)
  }

  // Listen for new user notification events coming from the server
  if (auth()) {
    const user = authUser()

    if (user) {
      echo
        .private(`notifications.${user.id}`)
        .listen('NotificationEvent', (payload: { notifications: { unread: number } }) => {
          unreadCount.value = payload.notifications.unread

          if ('setAppBadge' in navigator) {
            void navigator.setAppBadge(payload.notifications.unread)
          }
        })
    }
  }
})

onUpdated(() => {
  getFlashMessages()
})

watch(forceSnackBar, () => {
  if (forceSnackBar.value) {
    getFlashMessages()
    forceSnackBar.value = false
  }
})

function getFlashMessages() {
  const snack = getSnackBar()
  const flash = page.value.props.flash.message

  // Check for client side flash messages
  if (snack !== null && !isEmpty(snack)) {
    snackBar.message = snack.message ?? snackBar.message
    snackBar.active = snack.active ?? snackBar.active
    snackBar.color = snack.color ?? snackBar.color
  }

  // Check for server side flash messages
  if (flash !== null && !strNullOrEmpty(flash)) {
    snackBar.message = flash
    snackBar.active = true
    snackBar.color = 'primary'
  }
}
</script>

<template>
  <v-app>
    <po-head />
    <po-snack-bar />
    <po-login-modal v-model="loginModal" />
    <po-pwa-prompt />

    <v-toolbar
      color="primary"
      :elevation="0"
      border="b"
      class="po-navbar px-3 d-none d-lg-flex text-on-primary"
    >
      <v-container class="d-inline-flex justify-space-between">
        <div class="align-self-center">
          <po-link
            :href="route('home')"
            variant="plain"
            size="x-large"
            class="font-weight-bold"
            inertia
          >
            <v-img height="42" width="42" src="/images/logo.svg" />
          </po-link>
        </div>

        <v-tabs :model-value="page.props.route.name">
          <po-tab :href="route('explore')" value="explore" inertia>{{ $t('main.explore') }}</po-tab>
          <po-tab :href="route('writings.awards')" value="writings.awards" inertia>
            {{ $t('main.awards') }}
          </po-tab>
          <po-tab :href="route('writings.random')" value="writings.random" inertia>
            {{ $t('main.random') }}
          </po-tab>
          <po-tab :href="route('users.index')" value="users.index" inertia>
            {{ $t('users.authors') }}
          </po-tab>
          <po-tab @click.prevent="desktopSiteMenu = true">
            <v-icon icon="fas fa-ellipsis-vertical" />
            <v-menu v-model="desktopSiteMenu" target="parent">
              <v-list>
                <po-list-item
                  :href="route('contact.create')"
                  prepend-icon="fas fa-envelope"
                  inertia
                >
                  <span>{{ $t('main.contact-us') }}</span>
                </po-list-item>
                <v-divider class="my-0" />

                <po-list-item :href="faqPath()" prepend-icon="fas fa-circle-question" inertia>
                  <span>{{ $t('main.faq') }}</span>
                </po-list-item>
                <v-divider class="my-0" />

                <po-list-item :href="aboutPath()" prepend-icon="fas fa-address-card" inertia>
                  <span>{{ $t('main.about-us') }}</span>
                </po-list-item>
                <v-divider class="my-0" />

                <po-list-item :href="termsPath()" prepend-icon="fas fa-pen-ruler" inertia>
                  <span>{{ $t('main.terms-of-use') }}</span>
                </po-list-item>
                <v-divider class="my-0" />

                <po-list-item
                  :href="privacyPath()"
                  variant="text"
                  prepend-icon="fas fa-shield-halved"
                  inertia
                >
                  <span>{{ $t('main.privacy-policy') }}</span>
                </po-list-item>
              </v-list>
            </v-menu>
          </po-tab>
        </v-tabs>

        <div class="align-self-center d-flex align-center ga-3">
          <po-button
            v-if="!auth()"
            variant="text"
            color="on-surface-variant"
            :href="route('login')"
            style="font-size: 0.7rem"
            inertia
          >
            {{ $t('accounts.login-alt') }}
          </po-button>

          <po-button color="primary" variant="plain" :href="route('writings.create')" inertia>
            {{ $t('main.publish') }}
          </po-button>

          <v-menu v-if="auth()" target="parent">
            <template v-slot:activator="{ props }">
              <po-button icon v-bind="props" style="font-size: 0.7rem">
                <po-badge :count="unreadCount">
                  <po-avatar size="32" color="secondary" :user="authUser()!" />
                </po-badge>
              </po-button>
            </template>

            <v-list>
              <po-list-item :href="route('users.account')" prepend-icon="fas fa-user" inertia>
                <span>{{ $t('accounts.my-account') }}</span>
              </po-list-item>
              <v-divider class="my-0" />

              <po-list-item :href="route('notifications.index')" prepend-icon="fas fa-bell" inertia>
                <span>{{ $t('accounts.notifications') }}</span>
                <po-badge :count="unreadCount" inline />
              </po-list-item>
              <v-divider class="my-0" />

              <template v-if="admin()">
                <po-list-item :href="route('admin.index')" prepend-icon="fas fa-user-tie" inertia>
                  <span>{{ $t('main.administration') }}</span>
                </po-list-item>
                <v-divider class="my-0" />
              </template>

              <po-list-item
                :href="route('logout')"
                prepend-icon="fas fa-arrow-right-from-bracket"
                method="post"
                inertia
              >
                <span>{{ $t('accounts.logout') }}</span>
              </po-list-item>
            </v-list>
          </v-menu>
        </div>
      </v-container>
    </v-toolbar>

    <v-main class="mt-lg-16">
      <v-container class="po-container">
        <slot />
      </v-container>
    </v-main>

    <po-footer />
    <po-bottom-nav />
  </v-app>
</template>
