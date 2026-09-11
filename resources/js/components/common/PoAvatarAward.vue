<script setup lang="ts">
import type { UserLike } from '@/types/models'

defineProps<{
  user: UserLike
  avatarSize: string
  avatarColor: string
  avatarClass?: string
}>()
</script>

<template>
  <v-badge
    v-if="user.karma && ['A', 'B', 'C'].includes(user.karma)"
    icon="fas fa-award"
    color="transparent"
    :text-color="$helper.karmaMedal(user.karma) ?? undefined"
    location="bottom end"
    offset-x="8"
    offset-y="8"
  >
    <po-avatar :size="avatarSize" :color="avatarColor" :class="avatarClass" :user="user" />
  </v-badge>
</template>
