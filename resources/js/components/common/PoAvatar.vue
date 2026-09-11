<script setup lang="ts">
import { computed } from 'vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import type { UserLike } from '@/types/models'

const props = defineProps<{
  user: UserLike
}>()

const { strNullOrEmpty } = useTypeGuards()
const { storage, userDisplayName, userInitials } = useFormatting()

const avatar = computed(() => {
  if (
    props.user.avatar !== null &&
    props.user.avatar !== undefined &&
    props.user.avatar.trim() !== ''
  ) {
    return props.user.avatar
  } else if (
    props.user.extra_info?.avatar !== null &&
    props.user.extra_info?.avatar !== undefined &&
    props.user.extra_info.avatar !== ''
  ) {
    return props.user.extra_info.avatar
  }
  return ''
})
</script>

<template>
  <v-avatar>
    <v-img
      v-if="!strNullOrEmpty(avatar)"
      :src="storage(avatar)"
      :alt="userDisplayName(user)"
    ></v-img>
    <span v-else>{{ userInitials(user) }}</span>
  </v-avatar>
</template>
