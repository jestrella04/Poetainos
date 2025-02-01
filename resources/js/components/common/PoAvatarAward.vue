<script setup>
const props = defineProps({
  user: { type: Object, required: true },
  avatarSize: { type: String, required: true },
  avatarColor: { type: String, required: true },
  avatarClass: { type: String, required: false }
})
</script>

<template>
  <v-badge
    v-if="user.karma && ['A', 'B', 'C'].includes(user.karma)"
    icon="fas fa-award"
    color="transparent"
    :text-color="$helper.karmaMedal(user.karma)"
    location="bottom end"
    offset-x="8"
    offset-y="8"
  >
    <po-avatar :size="avatarSize" :color="avatarColor" :class="avatarClass" :user="user" />
  </v-badge>
</template>
