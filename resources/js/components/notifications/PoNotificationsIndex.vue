<script setup>
import { computed, inject, ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { useSwipe } from '@vueuse/core'

const page = computed(() => usePage())
const helper = inject('helper')
const notifications = ref([])
const next = ref('')
const unreadCount = inject('unreadCount')
const fetched = ref(false)
const target = document.body

useSwipe(
  target,
  {
    passive: true,
    onSwipe() {
      //
    },
    onSwipeEnd(e, direction) {
      if (direction === 'left') {
        swipeRight()
      } else if (direction === 'right') {
        swipeLeft()
      }
    },
  },
)

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

function swipeRight() {
  if ("unread" === page.value.props.tab) {
    document.querySelector('.v-tab[value="all"]').click()
  }
}

function swipeLeft() {
  if ("all" === page.value.props.tab) {
    document.querySelector('.v-tab[value="unread"]').click()
  }
}

onMounted(async () => {
  await router.reload({
    only: ['notifications'],
    onSuccess: (page) => {
      update(page.props.notifications.data, page.props.notifications.next_page_url)
    }
  })
})

function update(notificationsData, nextPage) {
  notifications.value.push(...notificationsData)
  next.value = nextPage
  fetched.value = true
}
</script>

<style scoped>
.column-full {
  width: 100%;
  max-width: 620px;
}
</style>

<template>
  <po-head />

  <v-row class="sticky-tabs mb-5 flex-grow-0">
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

  <div class="mx-auto column-full">
    <template v-if="!fetched">
      <po-loading type="avatar, paragraph, button" cols="12" md="12" lg="12" class="mx-auto"></po-loading>
    </template>

    <template v-else-if="!$helper.isEmpty(notifications)">
      <template v-if="'unread' === page.props.tab">
        <div class="mb-3 text-right">
          <po-button :href="$route('notifications.clear')" size="x-small" method="post" inertia
            @click="unreadCount = 0">
            <v-icon icon="fas fa-check-double" class="me-2"></v-icon>
            {{ $t('main.mark-all-read') }}
          </po-button>
        </div>
      </template>

      <template v-for="notification in notifications" :key="notification.id">
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

      <po-infinite-scroll @load="loadMore"></po-infinite-scroll>
    </template>

    <template v-else>
      <po-msg-block class="py-15" msg-title="" :msg-body="$t('accounts.notifications-empty')"
        icon="fas fa-bell-slash"></po-msg-block>
    </template>
  </div>
</template>