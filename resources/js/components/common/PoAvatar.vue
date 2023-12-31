<script setup>
defineProps({
  user: { type: Object, required: true }
})
</script>

<template>
  <v-avatar>
    <v-img v-if="!$helper.strNullOrEmpty(user.avatar)" :src="$helper.storage(user.avatar)"
      :alt="$helper.userDisplayName(user)"></v-img>
    <span v-else>{{ $helper.userInitials(user) }}</span>
  </v-avatar>
</template>