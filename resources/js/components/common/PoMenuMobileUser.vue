<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

const page = computed(() => usePage())
const mobileUserMenu = inject('mobileUserMenu', false)
</script>

<template>
  <v-bottom-sheet v-model="mobileUserMenu" inset>
    <v-card>
      <v-card-text>
        <v-list>
          <v-list-item>
            <po-button :href="$route('users.account', $helper.authUser())" variant="text" prepend-icon="fas fa-user"
              inertia rounded>
              <span>{{ $t('accounts.my-account') }}</span>
            </po-button>
          </v-list-item>
          <v-divider class="my-0"></v-divider>

          <v-list-item>
            <po-button :href="$route('notifications.list.unread')" variant="text" prepend-icon="fas fa-bell" inertia
              rounded>
              <span>{{ $t('accounts.notifications') }}</span>
              <po-badge :count="page.props.auth.notifications" inline>
              </po-badge>
            </po-button>
          </v-list-item>
          <v-divider class="my-0"></v-divider>

          <template v-if="$helper.admin()">
            <v-list-item>
              <po-button :href="$route('admin.index')" variant="text" prepend-icon="fas fa-user-tie" inertia rounded>
                <span>{{ $t('main.administration') }}</span>
              </po-button>
            </v-list-item>
            <v-divider class="my-0"></v-divider>
          </template>

          <v-list-item>
            <po-button :href="$route('logout')" variant="text" method="post"
              prepend-icon="fas fa-arrow-right-from-bracket" inertia rounded>
              <span>{{ $t('accounts.logout') }}</span>
            </po-button>
          </v-list-item>
        </v-list>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
</template>