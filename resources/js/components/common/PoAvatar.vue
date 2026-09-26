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

const avatar = computed(() => props.user.avatar?.trim() ?? '')
</script>

<template>
  <v-avatar>
    <v-img v-if="!strNullOrEmpty(avatar)" :src="storage(avatar)" :alt="userDisplayName(user)" />
    <span v-else>{{ userInitials(user) }}</span>
  </v-avatar>
</template>
