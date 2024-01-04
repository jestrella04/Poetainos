<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = computed(() => usePage())
</script>

<template>
  <po-head />

  <v-row class="mb-5">
    <v-col cols="12">
      <v-tabs v-model="page.props.tab" fixed-tabs>
        <v-tab href="?tab=unread" @click.prevent="$inertia.get('?tab=unread')" value="unread">
          <v-icon icon="fas fa-envelope" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.unread') }}</span>
        </v-tab>

        <v-tab href="?tab=all" @click.prevent="$inertia.get('?tab=all')" value="all">
          <v-icon icon="fas fa-envelope-open" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.all') }}</span>
        </v-tab>
      </v-tabs>
    </v-col>
  </v-row>

  <template v-if="$helper.isEmpty(page.props.notifications.data)">
    <po-msg-block :msg-title="$t('accounts.notifications-empty')" msg-body="" icon="fas fa-bell-slash"
      class="py-15"></po-msg-block>
  </template>

  <div v-else class="pe-3 mx-auto" style="width: 100%; max-width: 620px;">
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
              <p class="text-caption font-weight-medium">{{ $helper.toLocaleDate(notification.created_at) }}</p>
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
</template>