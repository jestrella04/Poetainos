<script setup>
import { ref, inject, provide } from 'vue'

const user = inject('user')
const sharer = ref(false)
const complainer = ref(false)
const blocker = ref(false)

provide('complainer', complainer)
provide('blocker', blocker)

function share() {
  if (navigator.share) {
    navigator.share({
      title: user.title,
      url: window.route('users.show', [user.username]),
    });
  } else {
    sharer.value = true
  }
}
</script>

<template>
  <po-sharer v-model="sharer" :link-title="$helper.userDisplayName(user)"
    :link-url="$route('users.show', [user.username])"></po-sharer>
  <po-complainer v-model="complainer" comp-type="users" :comp-id="user.id"></po-complainer>
  <po-blocker v-model="blocker" :user="user"></po-blocker>

  <v-menu>
    <template v-slot:activator="{ props }">
      <v-btn v-bind="props" icon="fas fa-ellipsis-vertical" color="secondary" size="x-small" variant="tonal"
        style="position: absolute; top: 1rem; right: 1rem; z-index: 999;"></v-btn>
    </template>

    <v-list>
      <po-list-item prepend-icon="fas fa-share-nodes" @click="share">
        <span>{{ $t('main.share-profile') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('users.edit', [user.username])" prepend-icon="fas fa-user-pen" inertia>
        <span>{{ $t('accounts.update-profile') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('users.writings.index', [user.username])" prepend-icon="fas fa-feather" inertia>
        <span>{{ $helper.authUser().id === user.id ? $t('users.view-self-writings') : $t('users.view-writings') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('users.shelf.index', [user.username])" prepend-icon="fas fa-bookmark" inertia>
        <span>{{ $helper.authUser().id === user.id ? $t('users.view-self-shelf') : $t('users.view-shelf') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item prepend-icon="fas fa-flag" @click.prevent="complainer = true">
        <span>{{ $t('complaints.report-user') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <template v-if="$helper.auth() && $helper.authUser().username !== user.username">
        <po-list-item prepend-icon="fas fa-ban" @click.prevent="blocker = true">
          <span>{{ $t('main.block-user') }}</span>
        </po-list-item>
      </template>
    </v-list>
  </v-menu>
</template>