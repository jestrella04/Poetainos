<script setup lang="ts">
import { mobileUserMenuKey, unreadCountKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'

const { admin } = useAuth()
const unreadCount = injectStrict(unreadCountKey)
const mobileUserMenu = injectStrict(mobileUserMenuKey)
</script>

<template>
  <v-bottom-sheet v-model="mobileUserMenu" inset close-on-content-click>
    <v-card>
      <v-card-text>
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
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
</template>
