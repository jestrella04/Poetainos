<script setup lang="ts">
import { computed } from 'vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useUserDisplay } from '@/composables/useUserDisplay'
import type { UserLike } from '@/types/models'

const props = defineProps<{
  user: UserLike
}>()

const { isBlank } = useTypeGuards()
const { userDisplayName, userInitials } = useUserDisplay()

const avatarUrl = computed(() => props.user.avatar_url ?? '')
</script>

<template>
  <v-avatar>
    <v-img v-if="!isBlank(avatarUrl)" :src="avatarUrl" :alt="userDisplayName(user)" />
    <span v-else>{{ userInitials(user) }}</span>
  </v-avatar>
</template>
