<script setup>
import { inject, ref, provide, reactive } from 'vue'
import PoUserDelete from './partials/PoUserDelete.vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const helper = inject('helper')
const push = inject('push')

if (!helper) {
  throw new Error('helper plugin not provided')
}

const username = helper.authUser().username
const isDelete = ref(false)
const notifications = reactive({
  email: (page.props.notifications?.email ?? true),
  push: false
})

provide('isDelete', isDelete)

if (typeof navigator !== 'undefined' && 'serviceWorker' in navigator) {
  navigator.serviceWorker.ready.then((registration) => {
    registration.pushManager
      .getSubscription()
      .then((subscription) => {
        // Keep subscription in sync with server
        if (subscription) {
          push.subscribe()
          notifications.push = true
        }

        // Uncheck the push switcher
        if (!subscription) {
          notifications.push = false
        }
      })
      .catch((e) => {
        console.log('Error thrown checking subscription status.', e)
      })
  }).catch((e) => {
    console.log('Service worker not available:', e)
  })
}

function email() {
  notifications.email = !notifications.email

  axios
    .post(route('notifications.email', [notifications.email]))
    .then()
    .catch()
    .finally()
}

function pusher() {
  notifications.push = !notifications.push

  if (notifications.push) {
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
        <div class="d-flex mb-5 pos-relative">
          <div class="d-flex ga-4 mb-2">
            <div>
              <po-avatar size="48" color="secondary" :user="$helper.authUser()" />
            </div>

            <div>
              <p class="font-weight-bold">
                <po-link
                  :href="route('users.show', $helper.authUser().username)"
                  class="stretched"
                  inertia
                >
                  {{ $helper.userDisplayName($helper.authUser()) }}
                </po-link>
              </p>
              <p class="text-medium-emphasis">@{{ $helper.authUser().username }}</p>
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
            v-model="notifications.email"
            :label="$t('main.email')"
            class="mb-0"
            hide-details="auto"
            color="primary"
            @click.prevent="email"
          ></v-switch>

          <v-switch
            v-model="notifications.push"
            :label="$t('main.push')"
            class="mb-0"
            hide-details="auto"
            color="primary"
            @click.prevent="pusher"
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
