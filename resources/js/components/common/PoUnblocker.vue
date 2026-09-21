<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { forceSnackBarKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useSnackbar } from '@/composables/useSnackbar'
import type { UserLike } from '@/types/models'

const props = defineProps<{
  user: UserLike
}>()

const { setSnackBar } = useSnackbar()
const forceSnackBar = injectStrict(forceSnackBarKey)
const isPosting = ref(false)

async function unblock(): Promise<void> {
  isPosting.value = true

  try {
    await axios.delete(route('users.unblock', props.user.username))

    setSnackBar({ message: 'users.user-unblocked', color: 'success', active: true })
    forceSnackBar.value = true
    router.reload()
  } catch {
    setSnackBar({ message: 'main.error-try-again', color: 'error', active: true })
    forceSnackBar.value = true
  } finally {
    isPosting.value = false
  }
}
</script>

<template>
  <po-button color="primary" variant="tonal" :disabled="isPosting" @click.prevent="unblock">
    <span v-if="!isPosting">{{ $t('main.unblock') }}</span>
    <v-progress-circular v-else indeterminate size="20" />
  </po-button>
</template>
