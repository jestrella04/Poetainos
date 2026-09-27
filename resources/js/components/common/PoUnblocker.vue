<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useRequestFailure } from '@/composables/useRequestFailure'
import type { UserLike } from '@/types/models'

const props = defineProps<{
  user: UserLike
}>()

const { onHttpException, onNetworkError } = useRequestFailure()
const form = useForm({})

// The server reloads the page with the author's content back and confirms with a flash message
function unblock(): void {
  form.delete(route('users.unblock', props.user.username), {
    preserveScroll: true,
    onHttpException,
    onNetworkError
  })
}
</script>

<template>
  <po-button color="primary" variant="tonal" :disabled="form.processing" @click.prevent="unblock">
    <span v-if="!form.processing">{{ $t('main.unblock') }}</span>
    <v-progress-circular v-else indeterminate size="20" />
  </po-button>
</template>
