<script setup lang="ts">
import type { UserLike } from '@/types/models'

defineProps<{
  user: UserLike
}>()
</script>

<template>
  <v-sheet class="d-flex align-center mx-auto" height="500" width="500" color="transparent">
    <po-msg-block
      :msg-title="$t('users.user-is-blocked')"
      :msg-body="$t('main.author-blocked')"
      icon="fas fa-ban"
    >
      <po-unblocker :user="user" />
    </po-msg-block>
  </v-sheet>
</template>
