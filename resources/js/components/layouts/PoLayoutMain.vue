<script setup>
import { computed, ref, reactive, provide, inject, onMounted, onUpdated, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from 'vuetify'
import { useRegisterSW } from 'virtual:pwa-register/vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import '@khmyznikov/pwa-install'

const page = computed(() => usePage())
const helper = inject('helper')
const theme = useTheme()
const desktopSiteMenu = ref(false)
const mobileUserMenu = ref(false)
const mobileSiteMenu = ref(false)
const forceSnackBar = ref(false)
const unreadCount = ref(page.value.props.auth.notifications)
const loginModal = ref(false)
const installComponent = document.createElement("pwa-install")
const snackBar = reactive({
  active: false,
  avatar: '/images/logo.svg',
  color: 'info',
  timeout: 6000,
  message: '',
})
const echo = new Echo({
  broadcaster: "pusher",
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  wsHost: import.meta.env.VITE_PUSHER_HOST,
  wsPort: import.meta.env.VITE_PUSHER_PORT,
  wssPort: import.meta.env.VITE_PUSHER_PORT,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: import.meta.env.VITE_PUSHER_APP_FORCETLS === "true",
  disableStats: true,
})
const reloadSW = '__RELOAD_SW__'
const intervalMS = 60 * 60 * 1000

echo.Pusher = Pusher
document.body.appendChild(installComponent)
window.matchMedia("(prefers-color-scheme: dark)").matches ? theme.change('dark') : theme.change('light')

useRegisterSW({
  onRegisteredSW(swUrl, r) {
    console.log(`Service Worker at: ${swUrl}`)

    if (reloadSW === 'true') {
      r && setInterval(async () => {
        console.log('Checking for sw update')
        await r.update()
      }, intervalMS)
    } else {
      console.log(`SW Registered: ${r}`)
    }
  },
})

provide('snackBar', snackBar)
provide('forceSnackBar', forceSnackBar)
provide('mobileSiteMenu', mobileSiteMenu)
provide('mobileUserMenu', mobileUserMenu)
provide('unreadCount', unreadCount)
provide('loginModal', loginModal)

onMounted(() => {
  getFlashMessages()

  if (helper.auth() && 'setAppBadge' in navigator) {
    navigator.setAppBadge(unreadCount.value)
  }

  // Listen for new user notification events coming from the server
  if (helper.auth()) {
    echo.private(`notifications.${helper.authUser().id}`).listen(
      "NotificationEvent",
      (payload) => {
        unreadCount.value = payload.notifications.unread

        if ('setAppBadge' in navigator) {
          navigator.setAppBadge(payload.notifications.unread);
        }
      }
    )
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
  const snack = helper.getSnackBar()
  const flash = page.value.props.flash.message

  // Check for client side flash messages
  if (!helper.isEmpty(snack)) {
    snackBar.message = snack.message
    snackBar.active = snack.active
    snackBar.color = snack.color
  }

  // Check for server side flash messages
  if (!helper.strNullOrEmpty(flash)) {
    snackBar.message = flash
    snackBar.active = true
    snackBar.color = 'primary'
  }
}
</script>

<style>
html {
  font-family:
    system-ui,
    -apple-system,
    "Segoe UI",
    Roboto,
    "Helvetica Neue",
    "Noto Sans",
    "Liberation Sans",
    Arial, sans-serif,
    "Apple Color Emoji",
    "Segoe UI Emoji",
    "Segoe UI Symbol",
    "Noto Color Emoji" !important;
  font-size: clamp(1.13rem, 1.08rem + 0.24vw, 1.25rem) !important;
}

html,
body,
.po-container {
  height: 100% !important;
}

pre,
code {
  font-family:
    SFMono-Regular,
    Menlo, Monaco,
    Consolas,
    "Liberation Mono",
    "Courier New",
    monospace !important;
}

@media screen and (min-width: 1280px) {
  .po-navbar {
    position: fixed !important;
    z-index: 999 !important;
    min-height: 64px !important;
  }
}

.logo-shadow {
  filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.2));
}

.avatar-shadow {
  filter: drop-shadow(0 0 10px rgba(0, 0, 0, 0.9));
}

.logo-shadow:focus,
.logo-shadow:active,
.logo-shadow:hover {
  filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
}

.pos-relative {
  position: relative !important;
}

.stretched::after {
  position: absolute !important;
  top: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  left: 0 !important;
  z-index: 1 !important;
  content: "" !important;
}

.v-card-text {
  font-size: 1rem !important;
}

.v-input {
  margin-bottom: 1rem;
}

.v-tab {
  font-size: 0.7rem !important;
}

.liked i,
.do-like:hover i {
  color: #D32F2F;
}

.shelved i,
.do-shelf:hover i {
  color: #2196F3;
}

.do-shelf,
.do-like {
  cursor: pointer;
}

.po-btn-more {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 999;
}

.smaller,
.smaller .v-card-text {
  font-size: 0.85rem !important;
}

.sticky-tabs {
  position: sticky !important;
  top: 64px !important;
  z-index: 990;
  background-color: rgb(var(--v-theme-background));
  margin-bottom: .8rem;
}

@media screen and (max-width: 1280px) {
  .sticky-tabs {
    top: 0 !important;
  }
}

.user-container,
.writing-container {
  width: 100% !important;
  max-width: 35rem;
  margin: 0 auto !important;
  margin-bottom: 1.5rem !important;
}
</style>

<template>
  <v-app>
    <po-head />
    <po-snack-bar></po-snack-bar>
    <po-login-modal v-model="loginModal"></po-login-modal>

    <v-toolbar color="primary" :elevation="8" class="po-navbar px-3 d-none d-lg-flex">
      <v-container class="d-inline-flex justify-space-between">
        <div class="align-self-center">
          <po-link :href="route('home')" variant="plain" size="x-large" class="font-weight-bold" inertia>
            <v-img height="42" width="42" src="/images/logo.svg" class="logo-shadow"></v-img>
          </po-link>
        </div>

        <v-tabs v-model="page.props.route.name">
          <po-tab :href="route('explore')" value="explore" inertia>{{ $t('main.explore') }}</po-tab>
          <po-tab :href="route('writings.awards')" value="writings.awards" inertia>{{ $t('main.awards') }}</po-tab>
          <po-tab :href="route('writings.random')" value="writings.random" inertia>{{ $t('main.random') }}</po-tab>
          <po-tab :href="route('users.index')" value="users.index" inertia>{{ $t('users.authors') }}</po-tab>
          <po-tab :href="route('writings.create')" value="writings.create" inertia>{{ $t('main.publish') }}</po-tab>
          <po-tab @click.prevent="desktopSiteMenu = true">
            <v-icon icon="fas fa-ellipsis-vertical"></v-icon>
            <v-menu v-model="desktopSiteMenu" target="parent">
              <v-list>
                <po-list-item :href="route('contact.create')" prepend-icon="fas fa-envelope" inertia>
                  <span>{{ $t('main.contact-us') }}</span>
                </po-list-item>
                <v-divider class="my-0"></v-divider>

                <po-list-item :href="route('pages.show', 'preguntas-frecuentes')" prepend-icon="fas fa-circle-question"
                  inertia>
                  <span>{{ $t('main.faq') }}</span>
                </po-list-item>
                <v-divider class="my-0"></v-divider>

                <po-list-item :href="route('pages.show', 'sobre-nosotros')" prepend-icon="fas fa-address-card" inertia>
                  <span>{{ $t('main.about-us') }}</span>
                </po-list-item>
                <v-divider class="my-0"></v-divider>

                <po-list-item :href="route('pages.show', 'condiciones-de-uso')" prepend-icon="fas fa-pen-ruler" inertia>
                  <span>{{ $t('main.terms-of-use') }}</span>
                </po-list-item>
                <v-divider class="my-0"></v-divider>

                <po-list-item :href="route('pages.show', 'politicas-de-privacidad')" variant="text"
                  prepend-icon="fas fa-shield-halved" inertia>
                  <span>{{ $t('main.privacy-policy') }}</span>
                </po-list-item>
              </v-list>
            </v-menu>
          </po-tab>
        </v-tabs>

        <div v-if="!$helper.auth()" class="align-self-center">
          <po-button prepend-icon="fas fa-arrow-right-to-bracket" variant="tonal" :href="route('login')"
            style="font-size: 0.7rem;" inertia>
            {{ $t('accounts.login-alt') }}
          </po-button>
        </div>

        <div v-else class="align-self-center">
          <v-menu target="parent">
            <template v-slot:activator="{ props }">
              <po-button icon v-bind="props" style="font-size: 0.7rem;">
                <po-badge :count="unreadCount">
                  <po-avatar size="32" color="secondary" :user="$helper.authUser()" />
                </po-badge>
              </po-button>
            </template>

            <v-list>
              <po-list-item :href="route('users.account')" prepend-icon="fas fa-user" inertia>
                <span>{{ $t('accounts.my-account') }}</span>
              </po-list-item>
              <v-divider class="my-0"></v-divider>

              <po-list-item :href="route('notifications.index')" prepend-icon="fas fa-bell" inertia>
                <span>{{ $t('accounts.notifications') }}</span>
                <po-badge :count="unreadCount" inline></po-badge>
              </po-list-item>
              <v-divider class="my-0"></v-divider>

              <template v-if="$helper.admin()">
                <po-list-item :href="route('admin.index')" prepend-icon="fas fa-user-tie" inertia>
                  <span>{{ $t('main.administration') }}</span>
                </po-list-item>
                <v-divider class="my-0"></v-divider>
              </template>

              <po-list-item :href="route('logout')" prepend-icon="fas fa-arrow-right-from-bracket" method="post"
                inertia>
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

    <po-footer></po-footer>
    <po-bottom-nav></po-bottom-nav>
  </v-app>
</template>
