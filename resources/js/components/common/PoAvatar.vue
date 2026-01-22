<script setup>
import { inject, ref, computed } from 'vue'

const props = defineProps({
  user: { type: Object, required: true }
})

const helper = inject('helper')

if (!helper) {
  throw new Error('helper plugin not provided')
}

const avatar = computed(() => {
  if (!helper.strNullOrEmpty(props.user?.avatar)) {
    return props.user.avatar
  } else if (props.user?.extra_info?.avatar) {
    return props.user.extra_info.avatar
  }
  return ''
})
</script>

<template>
  <v-avatar>
    <v-img
      v-if="!helper.strNullOrEmpty(avatar)"
      :src="$helper.storage(avatar)"
      :alt="$helper.userDisplayName(user)"
    ></v-img>
    <span v-else>{{ $helper.userInitials(user) }}</span>
  </v-avatar>
</template>
