<script setup lang="ts">
import { computed } from 'vue'
import { helperKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import type { UserLike } from '@/types/models'

const props = defineProps<{
  user: UserLike
}>()

const helper = injectStrict(helperKey)

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
      v-if="!helper.strNullOrEmpty(avatar)"
      :src="$helper.storage(avatar)"
      :alt="$helper.userDisplayName(user)"
    ></v-img>
    <span v-else>{{ $helper.userInitials(user) }}</span>
  </v-avatar>
</template>
