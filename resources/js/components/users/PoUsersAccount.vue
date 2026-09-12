<script setup lang="ts">
import { ref, provide, reactive } from 'vue'
import PoUserDelete from './partials/PoUserDelete.vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { isDeleteKey, pushKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useFormatting } from '@/composables/useFormatting'
import type { InertiaPageProps } from '@/types/inertia'

const page = usePage<InertiaPageProps<{ notifications?: { email: boolean } }>>()
const { authUser } = useAuth()
const { userDisplayName } = useFormatting()
const push = injectStrict(pushKey)

// This page is behind the `verified` auth middleware (routes/web.php), so
// the authenticated user is always present here.
const username = authUser()!.username
const isDelete = ref(false)
const notifications = reactive({
  email: page.props.notifications?.email ?? true,
  push: false
})

provide(isDeleteKey, isDelete)

void push.isSubscribed().then((isSubscribed) => {
  if (isSubscribed === true) {
    // Keep subscription in sync with server
    push.subscribe()
    notifications.push = true
  } else {
    notifications.push = false
  }
})

function toggleEmailNotifications(value: boolean | null): void {
  notifications.email = value === true

  void axios.post(route('notifications.email', [String(notifications.email)]))
}

function togglePushNotifications(value: boolean | null): void {
  notifications.push = value === true

  if (notifications.push === true) {
    push.subscribe()
  } else {
    push.unsubscribe()
  }
}
</script>

<template>
  <po-wrapper>
    <po-head></po-head>

    <v-card>
      <v-card-text style="max-width: 400px">
        <div class="d-flex mb-5 position-relative">
          <div class="d-flex ga-4 mb-2">
            <div>
              <po-avatar size="48" color="secondary" :user="authUser()!" />
            </div>

            <div>
              <p class="font-weight-bold">
                <po-link
                  :href="route('users.show', authUser()!.username)"
                  class="stretched"
                  inertia
                >
                  {{ userDisplayName(authUser()!) }}
                </po-link>
              </p>
              <p class="text-medium-emphasis">@{{ authUser()!.username }}</p>
            </div>
          </div>
        </div>
        <div class="mb-5">
          <p class="text-caption text-uppercase text-disabled">
            {{ $t('accounts.my-account') }}
          </p>

          <v-list>
            <po-list-item :href="route('users.edit', username)" inertia>
              {{ $t('accounts.update-profile') }}
            </po-list-item>

            <po-list-item :href="route('users.writings.index', username)" inertia>
              {{ $t('users.view-self-writings') }}
            </po-list-item>

            <po-list-item :href="route('users.shelf.index', username)" inertia>
              {{ $t('users.view-self-shelf') }}
            </po-list-item>

            <po-list-item :href="route('users.likes.index', username)" inertia>
              {{ $t('users.view-self-likes') }}
            </po-list-item>

            <po-list-item href="#" inertia disabled>
              {{ $t('accounts.manage-blocked-users') }}
            </po-list-item>
          </v-list>
        </div>
        <div class="mb-5">
          <p class="text-caption text-uppercase text-disabled mb-3">
            {{ $t('accounts.notifications') }}
          </p>

          <v-switch
            :model-value="notifications.email"
            :label="$t('main.email')"
            class="mb-0"
            hide-details="auto"
            color="primary"
            @update:model-value="toggleEmailNotifications"
          ></v-switch>

          <v-switch
            :model-value="notifications.push"
            :label="$t('main.push')"
            class="mb-0"
            hide-details="auto"
            color="primary"
            @update:model-value="togglePushNotifications"
          ></v-switch>
        </div>
        <div class="mb-5">
          <p class="text-caption text-uppercase text-disabled mb-3">
            {{ $t('accounts.danger-zone') }}
          </p>

          <po-button
            class="w-100 mb-1"
            color="secondary"
            size="small"
            :href="route('logout')"
            method="post"
            inertia
          >
            {{ $t('accounts.logout') }}
          </po-button>

          <po-button class="w-100 mb-1" color="error" size="small" @click.prevent="isDelete = true">
            {{ $t('accounts.delete-account') }}
          </po-button>

          <po-user-delete v-model="isDelete" :username="username"></po-user-delete>
        </div>
      </v-card-text>
    </v-card>
  </po-wrapper>
</template>
