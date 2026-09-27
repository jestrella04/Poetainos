<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { blockerKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useFormatting } from '@/composables/useFormatting'
import { useRequestFailure } from '@/composables/useRequestFailure'
import type { UserLike } from '@/types/models'

const props = defineProps<{
  user: UserLike
}>()

const { userDisplayName } = useFormatting()
const { onHttpException, onNetworkError } = useRequestFailure()
const blocker = injectStrict(blockerKey)
const form = useForm({})

// The server reloads the page without the author's content and confirms with a flash message
function submit(): void {
  form.post(route('users.block', props.user.username), {
    preserveScroll: true,
    onHttpException,
    onNetworkError,
    onSuccess: () => {
      blocker.value = false
    }
  })
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('main.block-user')">
      <po-modal-close @click.prevent="blocker = false" />

      <v-card-text>
        <p>
          {{ $t('accounts.block-user-warning') }}
          {{ $t('users.block-user-ask', { name: userDisplayName(user) }) }}
        </p>

        <v-divider class="mt-3" />

        <v-form id="blocking-form" @submit.prevent="submit">
          <po-button color="primary" type="submit" block :disabled="form.processing">
            <span v-if="!form.processing">{{ $t('main.block') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
