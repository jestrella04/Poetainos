<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { useFormatting } from '@/composables/useFormatting'
import type { UserLike } from '@/types/models'

defineProps<{
  users: UserLike[]
  size: number
  color: string
}>()

const { userDisplayName } = useFormatting()
</script>

<template>
  <div class="d-flex flex-wrap gr-2">
    <po-avatar
      v-for="user in users"
      :key="user.id"
      class="po-avatar-stacked me-n3"
      tag="a"
      border="md"
      :size="size"
      :color="color"
      :user="user"
      :href="route('users.show', user.username)"
      :title="userDisplayName(user)"
      @click.prevent="router.visit(route('users.show', user.username))"
    />
  </div>
</template>

<style scoped>
/* Lift-on-hover for the overlapping avatars; v-avatar-group has this built in but can't wrap on narrow screens. */
.po-avatar-stacked {
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.po-avatar-stacked:hover {
  transform: translateY(-8px);
}
</style>
