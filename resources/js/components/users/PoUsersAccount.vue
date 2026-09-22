<script setup lang="ts">
import { ref, provide, reactive } from 'vue'
import PoUserDelete from './partials/PoUserDelete.vue'
import PoUsersAccountRow from './partials/PoUsersAccountRow.vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { useDisplay } from 'vuetify'
import { isDeleteKey, pushKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useFormatting } from '@/composables/useFormatting'
import type { InertiaPageProps } from '@/types/inertia'

interface AccountSummary {
  created_at: string
  writings_count: number
  shelf_count: number
  likes_count: number
  blocked_authors_count: number
}

const page =
  usePage<InertiaPageProps<{ notifications?: { email: boolean }; account: AccountSummary }>>()
const { authUser } = useAuth()
const { userDisplayName, toLocaleMonthYear, formatCount } = useFormatting()
const push = injectStrict(pushKey)
const { mdAndUp } = useDisplay()

// This page is behind the `verified` auth middleware (routes/web.php), so
// the authenticated user is always present here.
const username = authUser()!.username
const account = page.props.account
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
    <po-head />

    <div class="d-flex flex-wrap align-center ga-5 pb-8">
      <po-avatar size="96" color="secondary" :user="authUser()!" />

      <div class="flex-grow-1">
        <p class="text-display-medium po-prose ma-0 mb-2">{{ userDisplayName(authUser()!) }}</p>
        <div class="d-inline-flex ga-6 text-medium-emphasis">
          <span>@{{ username }}</span>
          <span>
            {{ $t('accounts.member-since', { date: toLocaleMonthYear(account.created_at) }) }}</span
          >
        </div>
      </div>

      <po-link :href="route('users.show', username)" class="text-primary" inertia>
        {{ $t('accounts.view-public-profile') }}
      </po-link>
    </div>

    <v-divider />

    <v-row :gap="mdAndUp ? 60 : undefined">
      <v-col cols="12" md="6">
        <p class="text-uppercase text-eyebrow text-primary mt-12">
          {{ $t('accounts.my-account') }}
        </p>

        <div class="d-flex flex-column">
          <po-users-account-row
            :href="route('users.edit', username)"
            :title="$t('accounts.update-profile')"
            :subtitle="$t('accounts.update-profile-hint')"
          />

          <po-users-account-row
            :href="route('users.writings.index', username)"
            :title="$t('users.view-self-writings')"
            :subtitle="
              $t('accounts.writings-count', { count: formatCount(account.writings_count) })
            "
          />

          <po-users-account-row
            :href="route('users.shelf.index', username)"
            :title="$t('users.view-self-shelf')"
            :subtitle="$t('accounts.shelf-count', { count: formatCount(account.shelf_count) })"
          />

          <po-users-account-row
            :href="route('users.likes.index', username)"
            :title="$t('users.view-self-likes')"
            :subtitle="$t('accounts.likes-count', { count: formatCount(account.likes_count) })"
          />

          <po-users-account-row
            :href="route('users.blocked.index')"
            :title="$t('accounts.manage-blocked-users')"
            :subtitle="
              account.blocked_authors_count > 0
                ? $t('accounts.blocked-count', {
                    count: formatCount(account.blocked_authors_count)
                  })
                : $t('accounts.no-blocked-users')
            "
          />
        </div>
      </v-col>

      <v-col cols="12" md="6">
        <p class="text-uppercase text-eyebrow text-primary mt-md-12">
          {{ $t('accounts.notifications') }}
        </p>

        <div class="d-flex flex-column">
          <po-users-account-row
            :title="$t('main.email')"
            :subtitle="$t('accounts.email-notifications-hint')"
          >
            <v-switch
              :model-value="notifications.email"
              :aria-label="$t('main.email')"
              hide-details
              color="primary"
              @update:model-value="toggleEmailNotifications"
            />
          </po-users-account-row>

          <po-users-account-row
            :title="$t('main.push')"
            :subtitle="$t('accounts.push-notifications-hint')"
          >
            <v-switch
              :model-value="notifications.push"
              :aria-label="$t('main.push')"
              hide-details
              color="primary"
              @update:model-value="togglePushNotifications"
            />
          </po-users-account-row>
        </div>

        <p class="text-uppercase text-eyebrow text-primary mt-12">
          {{ $t('accounts.danger-zone') }}
        </p>

        <p class="text-title-medium text-medium-emphasis" style="max-width: 52ch">
          {{ $t('accounts.delete-account-summary') }}
        </p>

        <div class="d-flex ga-3">
          <po-button color="primary" variant="tonal" :href="route('logout')" method="post" inertia>
            {{ $t('accounts.logout') }}
          </po-button>

          <po-button color="error" variant="tonal" @click.prevent="isDelete = true">
            {{ $t('accounts.delete-account') }}
          </po-button>
        </div>
      </v-col>
    </v-row>

    <po-user-delete v-model="isDelete" :username="username" />
  </po-wrapper>
</template>
