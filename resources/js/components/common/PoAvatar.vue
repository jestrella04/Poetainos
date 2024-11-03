<script setup>
import { inject, ref } from 'vue'

const props = defineProps({
  user: { type: Object, required: true }
})

const helper = inject('helper')
const avatar = ref('')

if (!helper.strNullOrEmpty(props.user.avatar)) {
  avatar.value = props.user.avatar
} else if ('extra_info' in props.user) {
  avatar.value = props.user.extra_info.avatar
}
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
