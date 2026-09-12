<script setup lang="ts">
import { blockerKey, forceSnackBarKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useFormatting } from '@/composables/useFormatting'
import { useSnackbar } from '@/composables/useSnackbar'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { UserLike } from '@/types/models'

const props = defineProps<{
  user: UserLike
}>()

const { userDisplayName } = useFormatting()
const { setSnackBar } = useSnackbar()
const blocker = injectStrict(blockerKey)
const forceSnackBar = injectStrict(forceSnackBarKey)
const { isPosting, submitForm } = useFormSubmit(false)

async function submit(): Promise<void> {
  await submitForm({
    formSelector: '#blocking-form',
    payload: { user: props.user.username },
    onSuccess: () => {
      setSnackBar({
        message: 'users.user-blocked',
        color: 'success',
        active: true
      })

      forceSnackBar.value = true
      blocker.value = false
    },
    onError: () => true
  })
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('main.block-user')">
      <po-modal-close @click.prevent="blocker = false"></po-modal-close>

      <v-card-text>
        <p>
          {{ $t('accounts.block-user-warning') }}
          {{ $t('users.block-user-ask', { name: userDisplayName(user) }) }}
        </p>

        <v-divider class="mt-3"></v-divider>

        <v-form
          id="blocking-form"
          :action="route('users.block', user.username)"
          @submit.prevent="submit"
        >
          <po-button color="primary" type="submit" block>
            <span v-if="!isPosting">{{ $t('main.block') }}</span>
            <v-progress-circular v-else indeterminate></v-progress-circular>
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
