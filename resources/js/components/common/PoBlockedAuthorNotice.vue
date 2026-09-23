<script setup lang="ts">
import type { UserLike } from '@/types/models'

defineProps<{
  user: UserLike
}>()
</script>

<template>
  <div class="d-flex align-center mx-auto" style="height: 500px; width: 500px">
    <po-msg-block
      :msg-title="$t('users.user-is-blocked')"
      :msg-body="$t('main.author-blocked')"
      icon="fas fa-ban"
    >
      <po-unblocker :user="user" />
    </po-msg-block>
  </div>
</template>
