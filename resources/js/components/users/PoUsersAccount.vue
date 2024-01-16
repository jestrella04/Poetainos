<script setup>
import { inject, ref, provide } from 'vue'
import PoUserDelete from './partials/PoUserDelete.vue'

const helper = inject('helper')
const username = helper.authUser().username
const isDelete = ref(false)

provide('isDelete', isDelete)
</script>

<template>
  <po-head></po-head>
  <v-card>
    <v-card-text style="max-width: 400px;">
      <div class="d-flex mb-5 pos-relative">
        <div class="d-flex ga-4 mb-2">
          <div>
            <po-avatar size="48" color="secondary" :user="$helper.authUser()" />
          </div>

          <div>
            <p class="font-weight-bold">
              <po-link :href="$route('users.show', $helper.authUser().username)" class="stretched" inertia>
                {{ $helper.userDisplayName($helper.authUser()) }}
              </po-link>
            </p>
            <p class="text-medium-emphasis">@{{ $helper.authUser().username }}</p>
          </div>
        </div>
      </div>
      <div class=" mb-5">
        <p class="text-caption text-uppercase text-disabled">
          {{ $t('accounts.my-account') }}
        </p>

        <v-list>
          <po-list-item :href="$route('users.edit', username)" inertia>
            {{ $t('accounts.update-profile') }}
          </po-list-item>

          <po-list-item :href="$route('users.writings.index', username)" inertia>
            {{ $t('users.view-self-writings') }}
          </po-list-item>

          <po-list-item :href="$route('users.shelf.index', username)" inertia>
            {{ $t('users.view-self-shelf') }}
          </po-list-item>

          <po-list-item :href="$route('users.likes.index', username)" inertia>
            {{ $t('users.view-self-likes') }}
          </po-list-item>

          <po-list-item href="#" inertia disabled>
            {{ $t('accounts.manage-blocked-users') }}
          </po-list-item>
        </v-list>
      </div>
      <div class=" mb-5">
        <p class="text-caption text-uppercase text-disabled mb-3">{{ $t('accounts.notifications') }}</p>

        <v-switch :label="$t('main.email')" class="mb-0" hide-details="auto" color="primary"></v-switch>
        <v-switch :label="$t('main.push')" class="mb-0" hide-details="auto" color="primary"></v-switch>
      </div>
      <div class=" mb-5">
        <p class="text-caption text-uppercase text-disabled mb-3">{{ $t('accounts.danger-zone') }}</p>

        <po-button class="w-100 mb-1" color="secondary" size="small" :href="$route('logout')" method="post" inertia>
          {{ $t('accounts.logout') }}
        </po-button>

        <po-button class="w-100 mb-1" color="error" size="small" @click.prevent="isDelete = true">
          {{ $t('accounts.delete-account') }}
        </po-button>

        <po-user-delete v-model="isDelete" :username="username"></po-user-delete>
      </div>
    </v-card-text>
  </v-card>
</template>