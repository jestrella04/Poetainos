<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

const page = computed(() => usePage())
const mobileUserMenu = inject('mobileUserMenu', false)
</script>

<template>
  <v-bottom-sheet v-model="mobileUserMenu" inset close-on-content-click>
    <v-card>
      <v-card-text>
        <v-list>
          <po-list-item :href="$route('users.account', $helper.authUser())" prepend-icon="fas fa-user" inertia>
            <span>{{ $t('accounts.my-account') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="$route('notifications.index')" prepend-icon="fas fa-bell" inertia>
            <span>{{ $t('accounts.notifications') }}</span>
            <po-badge :count="page.props.auth.notifications" inline></po-badge>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <template v-if="$helper.admin()">
            <po-list-item :href="$route('admin.index')" prepend-icon="fas fa-user-tie" inertia>
              <span>{{ $t('main.administration') }}</span>
            </po-list-item>
            <v-divider class="my-0"></v-divider>
          </template>

          <po-list-item :href="$route('logout')" prepend-icon="fas fa-arrow-right-from-bracket" method="post" inertia>
            <span>{{ $t('accounts.logout') }}</span>
          </po-list-item>
        </v-list>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
</template>