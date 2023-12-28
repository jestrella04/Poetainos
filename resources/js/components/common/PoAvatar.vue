<script setup>
defineProps({
  size: { type: String, default: '64' },
  color: { type: String, default: 'primary' },
  user: { type: Object }
})
</script>

<template>
  <v-avatar :size="size" :color="color">
    <v-img v-if="!$helper.strNullOrEmpty(user.avatar)" :src="$helper.storage(user.avatar)"
      :alt="$helper.userDisplayName(user)"></v-img>
    <span v-else>{{ $helper.userInitials(user) }}</span>
  </v-avatar>
</template>