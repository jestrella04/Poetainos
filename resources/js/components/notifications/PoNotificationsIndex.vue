<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { unreadCountKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import { useNotificationMessage } from '@/composables/useNotificationMessage'
import { usePaginatedTabList } from '@/composables/usePaginatedTabList'
import type { InertiaPageProps } from '@/types/inertia'
import type { AppNotification } from '@/types/models'

const page = computed(() => usePage<InertiaPageProps<{ tab: string }>>())
const { isEmpty } = useTypeGuards()
const { relativeDate } = useFormatting()
const { notificationMessage } = useNotificationMessage()
const unreadCount = injectStrict(unreadCountKey)

const {
  items: notifications,
  fetched,
  loadMore
} = usePaginatedTabList<AppNotification>({
  tabOrder: ['unread', 'all'],
  currentTab: () => page.value.props.tab,
  reloadPropKey: 'notifications'
})
</script>

<style scoped>
/* Caps the list to a comfortable reading width; Vuetify's v-container has no such preset. */
.column-full {
  width: 100%;
  max-width: 620px;
}
</style>

<template>
  <po-head />

  <v-row class="sticky-tabs mb-5 flex-grow-0">
    <v-col cols="12">
      <v-tabs :model-value="page.props.tab" fixed-tabs>
        <po-tab href="?tab=unread" value="unread" :aria-label="$t('main.unread')" inertia>
          <v-icon icon="fas fa-envelope" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.unread') }}</span>
        </po-tab>

        <po-tab href="?tab=all" value="all" :aria-label="$t('main.read')" inertia>
          <v-icon icon="fas fa-envelope-open" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.read') }}</span>
        </po-tab>
      </v-tabs>
    </v-col>
  </v-row>

  <div class="mx-auto column-full">
    <template v-if="!fetched">
      <po-loading type="avatar, paragraph, button" cols="12" md="12" lg="12" class="mx-auto" />
    </template>

    <template v-else-if="!isEmpty(notifications)">
      <template v-if="'unread' === page.props.tab">
        <div class="mb-3 text-right">
          <po-button
            :href="route('notifications.clear')"
            size="x-small"
            method="post"
            inertia
            @click="unreadCount = 0"
          >
            <v-icon icon="fas fa-check-double" class="me-2" />
            {{ $t('main.mark-all-read') }}
          </po-button>
        </div>
      </template>

      <template v-for="notification in notifications" :key="notification.id">
        <v-card class="mb-3">
          <v-card-text>
            <div class="d-flex ga-5">
              <div>
                <template v-if="notification.notifier_user !== null">
                  <po-avatar size="48" color="secondary" :user="notification.notifier_user" />
                </template>
                <template v-else>
                  <v-avatar size="48" color="secondary" image="/images/logo.svg" />
                </template>
              </div>
              <div class="w-100">
                <p class="text-caption font-weight-medium">
                  {{ relativeDate(notification.created_at) }}
                </p>
                <div class="d-flex w-100 justify-space-between">
                  <div>
                    <p>{{ notificationMessage(notification, $t) }}.</p>
                    <p
                      v-if="notification.notifier_writing !== null"
                      class="text-caption text-disabled"
                    >
                      {{ $t('main.title') }}: {{ notification.notifier_writing.title }}
                    </p>
                  </div>
                  <div>
                    <po-button color="primary" size="small" variant="tonal">
                      {{ $t('main.view') }}
                    </po-button>
                    <po-link
                      :href="route('notifications.show', notification.id)"
                      class="stretched"
                      inertia
                    />
                  </div>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </template>

      <po-infinite-scroll @load="loadMore" />
    </template>

    <template v-else>
      <po-msg-block
        class="py-15"
        msg-title=""
        :msg-body="$t('accounts.notifications-empty')"
        icon="fas fa-bell-slash"
      />
    </template>
  </div>
</template>
