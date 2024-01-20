<script setup>
import { computed, inject, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = computed(() => usePage())
const helper = inject('helper')
const notifications = ref(page.value.props.notifications.data)
const next = ref(page.value.props.notifications.next_page_url)
const total = ref(page.value.props.notifications.total)
const unreadCount = inject('unreadCount')

async function loadMore({ done }) {
  if (!helper.strNullOrEmpty(next.value)) {
    await axios
      .get(next.value)
      .then((response) => {
        notifications.value.push(...response.data.data)
        next.value = response.data.next_page_url
        done('ok')
      })
      .catch(() => {
        done('error')
      })
  } else {
    done('empty')
  }
}
</script>

<template>
  <po-head />

  <v-row class="mb-5">
    <v-col cols="12">
      <v-tabs v-model="page.props.tab" fixed-tabs>
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

  <template v-if="'unread' === page.props.tab && total > 0">
    <div class="mx-auto mb-3 text-right" style="width: 100%; max-width: 620px;">
      <po-button :href="$route('notifications.clear')" size="x-small" method="post" inertia @click="unreadCount = 0">
        <v-icon icon="fas fa-check-double" class="me-2"></v-icon>
        {{ $t('main.mark-all-read') }}
      </po-button>
    </div>
  </template>

  <template v-if="$helper.isEmpty(page.props.notifications.data)">
    <po-msg-block :msg-title="$t('accounts.notifications-empty')" msg-body="" icon="fas fa-bell-slash"
      class="py-15"></po-msg-block>
  </template>

  <div v-else class="mx-auto" style="width: 100%; max-width: 620px;">
    <template v-for="notification in page.props.notifications.data" :key="notification.id">
      <v-card class="mb-3">
        <v-card-text>
          <div class="d-flex ga-5">
            <div>
              <template v-if="!$helper.isEmpty(notification.notifier_user)">
                <po-avatar size="48" color="secondary" :user="notification.notifier_user" />
              </template>
              <template v-else>
                <v-avatar size="48" color="secondary" image="/images/logo.svg" />
              </template>
            </div>
            <div class="w-100">
              <p class="text-caption font-weight-medium">{{ $helper.relativeDate(notification.created_at) }}</p>
              <div class="d-flex w-100 justify-space-between">
                <div>
                  <p>{{ $helper.notificationMessage(notification, $t) }}.</p>
                  <p class="text-caption text-disabled">
                    {{ $t('main.title') }}: {{ notification.notifier_writing.title }}
                  </p>
                </div>
                <div>
                  <po-button color="primary" size="small" variant="tonal">
                    {{ $t('main.view') }}
                  </po-button>
                  <po-link :href="$route('notifications.show', notification.id)" class="stretched" inertia> </po-link>
                </div>
              </div>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </template>
  </div>

  <po-infinite-scroll @load="loadMore"></po-infinite-scroll>
</template>