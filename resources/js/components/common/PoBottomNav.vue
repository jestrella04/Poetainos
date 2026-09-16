<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { mobileSiteMenuKey, mobileUserMenuKey, unreadCountKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'

const { auth, authUser } = useAuth()
const page = computed(() => usePage())
const mobileSiteMenu = injectStrict(mobileSiteMenuKey)
const mobileUserMenu = injectStrict(mobileUserMenuKey)
const unreadCount = injectStrict(unreadCountKey)
</script>

<template>
  <v-bottom-navigation
    v-model="page.props.route.name"
    bg-color="surface"
    :elevation="0"
    border="t"
    class="hidden-lg-and-up"
  >
    <po-button value="home" :href="route('home')" :title="$t('main.home')" inertia>
      <v-icon icon="fas fa-home" />
    </po-button>

    <po-button value="explore" :href="route('explore')" :title="$t('main.explore')" inertia>
      <v-icon icon="fas fa-wand-magic-sparkles" />
    </po-button>

    <po-button value="publish" :href="route('writings.create')" :title="$t('main.publish')" inertia>
      <v-icon icon="fas fa-plus" />
    </po-button>

    <template v-if="!auth()">
      <po-button value="login" :href="route('login')" :title="$t('accounts.login-alt')" inertia>
        <v-icon icon="fas fa-arrow-right-to-bracket" />
      </po-button>
    </template>

    <template v-else>
      <po-button
        value="account"
        :title="$t('accounts.my-account')"
        @click.prevent="mobileUserMenu = !mobileUserMenu"
      >
        <po-badge :count="unreadCount">
          <po-avatar size="24" color="secondary" :user="authUser()!" />
        </po-badge>
      </po-button>

      <po-menu-mobile-user />
    </template>

    <po-button
      value="menu"
      :title="$t('main.menu')"
      @click.prevent="mobileSiteMenu = !mobileSiteMenu"
    >
      <v-icon icon="fas fa-bars" />
    </po-button>

    <po-menu-mobile-site />
  </v-bottom-navigation>
</template>
