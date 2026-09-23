<script setup lang="ts">
import { computed } from 'vue'
import { useFormatting } from '@/composables/useFormatting'
import type { UserLike } from '@/types/models'

const props = defineProps<{
  user: UserLike
  avatarSize: string
  avatarColor: string
  avatarClass?: string
}>()

const { karmaMedal } = useFormatting()

const isAwardEligible = computed(
  () =>
    props.user.karma !== null &&
    props.user.karma !== undefined &&
    ['A', 'B', 'C'].includes(props.user.karma)
)
</script>

<template>
  <template v-if="isAwardEligible">
    <v-badge
      icon="fas fa-award"
      color="transparent"
      :text-color="karmaMedal(user.karma ?? '') ?? undefined"
      location="bottom end"
      offset-x="8"
      offset-y="8"
    >
      <po-avatar :size="avatarSize" :color="avatarColor" :class="avatarClass" :user="user" />
    </v-badge>
  </template>

  <template v-else>
    <po-avatar :size="avatarSize" :color="avatarColor" :class="avatarClass" :user="user" />
  </template>
</template>
