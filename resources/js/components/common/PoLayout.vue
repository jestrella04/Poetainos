<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from 'vuetify'
import PoHead from './PoHead.vue'
import PoAvatar from './PoAvatar.vue'

const page = computed(() => usePage())
const theme = useTheme()

theme.global.name.value = window.matchMedia("(prefers-color-scheme: dark)").matches ? 'dark' : 'light'
</script>

<style>
.navbar {
  position: fixed !important;
  z-index: 9999 !important;
}

.logo-shadow {
  filter: drop-shadow(0 0 2px rgba(248, 249, 250, .5));
}
</style>

<template>
  <po-head title="" />

  <v-app>
    <v-toolbar color="primary" class="navbar px-3 d-none d-lg-flex">
      <template v-slot:prepend>
        <v-toolbar-title>
          <v-btn variant="plain" size="x-large" class="text-bold" :href="$route('home')"
            @click.prevent="$inertia.get($route('home'))">
            <v-img width="32" src="/images/logo.svg" class="logo-shadow mr-2"></v-img>
            {{ page.props.site.name }}
          </v-btn>
        </v-toolbar-title>
      </template>

      <v-spacer></v-spacer>

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

      <v-spacer></v-spacer>

      <v-btn v-if="!$helper.auth()" prepend-icon="fas fa-arrow-right-to-bracket" variant="tonal"
        :href="$route('socialite')" @click.prevent="$inertia.get($route('socialite'))">
        {{ $t('accounts.login-alt') }}
      </v-btn>

      <v-menu v-else min-width="200px" rounded>
        <template v-slot:activator="{ props }">
          <v-btn icon v-bind="props">
            <po-avatar size="32" color="secondary" :user="$helper.authUser()" />
          </v-btn>
        </template>

        <v-card>
          <v-card-text>
            <div class="mx-auto text-center">
              <v-btn rounded block variant="text" :href="$route('users.account', $helper.authUser())"
                @click.prevent="$inertia.get($route('users.account', $helper.authUser()))">
                {{ $t('accounts.my-account') }}
              </v-btn>
              <v-divider class="my-3"></v-divider>

              <v-btn rounded block variant="text" :href="$route('notifications.list.unread')"
                @click.prevent="$inertia.get($route('notifications.list.unread'))">
                {{ $t('accounts.notifications') }}
              </v-btn>
              <v-divider class="my-3"></v-divider>

              <v-btn v-if="$helper.admin()" rounded block variant="text" :href="$route('admin.index')"
                @click.prevent="$inertia.get($route('admin.index'))">
                {{ $t('main.administration') }}
              </v-btn>
              <v-divider class="my-3"></v-divider>

              <v-btn rounded block variant="text" @click.prevent="$inertia.post($route('logout'))">
                {{ $t('accounts.logout') }}
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-menu>
    </v-toolbar>

    <v-main class="mt-lg-15">
      <v-container fluid>
        <slot />
      </v-container>
    </v-main>

    <v-bottom-navigation v-model="page.props.route.name" bg-color="primary" class="hidden-lg-and-up" mode="shift" grow>
      <v-btn value="home" :href="$route('home')" @click.prevent="$inertia.get($route('home'))">
        <v-icon icon="fas fa-home" />
        <span>{{ $t('main.home') }}</span>
      </v-btn>

      <v-btn value="explore" :href="$route('explore')" @click.prevent="$inertia.get($route('explore'))">
        <v-icon icon="fas fa-wand-magic-sparkles" />
        <span>{{ $t('main.explore') }}</span>
      </v-btn>

      <v-btn value="publish" :href="$route('writings.create')" @click.prevent="$inertia.get($route('writings.create'))">
        <v-icon icon="fas fa-pen-nib" />
        <span>{{ $t('main.publish') }}</span>
      </v-btn>

      <v-btn value="login" :href="$route('socialite')" @click.prevent="$inertia.get($route('socialite'))">
        <v-icon icon="fas fa-arrow-right-to-bracket" />
        <span>{{ $t('accounts.login-alt') }}</span>
      </v-btn>

      <v-btn value="menu">
        <v-icon icon="fas fa-bars" />
        <span><span>{{ $t('main.menu') }}</span></span>
      </v-btn>
    </v-bottom-navigation>
  </v-app>
</template>
