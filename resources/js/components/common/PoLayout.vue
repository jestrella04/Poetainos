<script setup>
import { computed, ref, provide } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from 'vuetify'

const page = computed(() => usePage())
const theme = useTheme()
const mobileUserMenu = ref(false)
const mobileSiteMenu = ref(false)

theme.global.name.value = window.matchMedia("(prefers-color-scheme: dark)").matches ? 'dark' : 'light'

provide('mobileSiteMenu', mobileSiteMenu)
provide('mobileUserMenu', mobileUserMenu)
</script>

<style>
.po-navbar {
  position: fixed !important;
  z-index: 999 !important;
}

.logo-shadow {
  filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.5));
}

.pos-relative {
  position: relative !important;
}

.stretched::after {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1;
  content: "";
}
</style>

<template>
  <po-head title="" />

  <v-app>
    <v-toolbar color="primary" :elevation="8" class="po-navbar px-3 d-none d-lg-flex">
      <v-container class="d-inline-flex justify-space-between">
        <div class="align-self-center">
          <po-button :href="$route('home')" variant="plain" size="x-large" class="font-weight-bold" inertia>
            <v-img width="32" src="/images/logo.svg" class="logo-shadow mr-2"></v-img>
            {{ page.props.site.name }}
          </po-button>
        </div>

        <v-tabs v-model="page.props.route.name" centered>
          <v-tab :text="$t('main.explore')" :href="$route('explore')" value="explore"
            @click.prevent="$inertia.get($route('explore'))"></v-tab>
          <v-tab :text="$t('main.awards')" :href="$route('writings.awards')" value="writings.awards"
            @click.prevent="$inertia.get($route('writings.awards'))"></v-tab>
          <v-tab :text="$t('main.random')" :href="$route('writings.random')" value="writings.random"
            @click.prevent="$inertia.get($route('writings.random'))"></v-tab>
          <v-tab :text="$t('users.authors')" :href="$route('users.index')" value="users.index"
            @click.prevent="$inertia.get($route('users.index'))"></v-tab>
          <v-tab :text="$t('main.publish')" :href="$route('writings.create')" value="writings.create"
            @click.prevent="$inertia.get($route('writings.create'))"></v-tab>
        </v-tabs>

        <div v-if="!$helper.auth()" class="align-self-center">
          <po-button prepend-icon="fas fa-arrow-right-to-bracket" variant="tonal" :href="$route('socialite')" inertia>
            {{ $t('accounts.login-alt') }}
          </po-button>
        </div>

        <div v-else class="align-self-center">
          <v-menu class="align-self-center" min-width="200px" rounded>
            <template v-slot:activator="{ props }">
              <po-button icon v-bind="props">
                <po-badge :count="page.props.auth.notifications">
                  <po-avatar size="32" color="secondary" :user="$helper.authUser()" />
                </po-badge>
              </po-button>
            </template>

            <v-card>
              <v-card-text>
                <div class="mx-auto text-center">
                  <po-button rounded block variant="text" :href="$route('users.account', $helper.authUser())" inertia>
                    {{ $t('accounts.my-account') }}
                  </po-button>
                  <v-divider class="my-3"></v-divider>

                  <po-button rounded block variant="text" :href="$route('notifications.list.unread')" inertia>
                    {{ $t('accounts.notifications') }}
                  </po-button>
                  <v-divider class="my-3"></v-divider>

                  <po-button v-if="$helper.admin()" rounded block variant="text" :href="$route('admin.index')" inertia>
                    {{ $t('main.administration') }}
                  </po-button>
                  <v-divider class="my-3"></v-divider>

                  <po-button rounded block variant="text" :href="$route('logout')" method="post" inertia>
                    {{ $t('accounts.logout') }}
                  </po-button>
                </div>
              </v-card-text>
            </v-card>
          </v-menu>
        </div>

      </v-container>
    </v-toolbar>

    <v-main class="mt-lg-16">
      <v-container>
        <slot />
      </v-container>
    </v-main>

    <v-bottom-navigation v-model="page.props.route.name" bg-color="primary" class="hidden-lg-and-up" mode="shift" grow>
      <po-button value="home" :href="$route('home')" inertia>
        <v-icon icon="fas fa-home" />
        <span>{{ $t('main.home') }}</span>
      </po-button>

      <po-button value="explore" :href="$route('explore')" inertia>
        <v-icon icon="fas fa-wand-magic-sparkles" />
        <span>{{ $t('main.explore') }}</span>
      </po-button>

      <po-button value="publish" :href="$route('writings.create')" inertia>
        <v-icon icon="fas fa-pen-nib" />
        <span>{{ $t('main.publish') }}</span>
      </po-button>

      <template v-if="!$helper.auth()">
        <po-button value="login" :href="$route('socialite')" inertia>
          <v-icon icon="fas fa-arrow-right-to-bracket" />
          <span>{{ $t('accounts.login-alt') }}</span>
        </po-button>
      </template>

      <template v-else>
        <po-button value="account" @click.prevent="mobileUserMenu = !mobileUserMenu">
          <po-badge :count="page.props.auth.notifications">
            <po-avatar size="24" color="secondary" :user="$helper.authUser()" />
          </po-badge>
          <span>{{ $t('accounts.my-account') }}</span>
        </po-button>

        <po-menu-mobile-user />
      </template>

      <po-button value="menu" @click.prevent="mobileSiteMenu = !mobileSiteMenu">
        <v-icon icon="fas fa-bars" />
        <span><span>{{ $t('main.menu') }}</span></span>
      </po-button>

      <po-menu-mobile-site />
    </v-bottom-navigation>
  </v-app>
</template>
