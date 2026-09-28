<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { isDeleteKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useFormValidation } from '@/composables/useFormValidation'
import { usePasswordConfirmation } from '@/composables/usePasswordConfirmation'
import { useRequestFailure } from '@/composables/useRequestFailure'

const props = defineProps<{
  username: string
}>()

const isDelete = injectStrict(isDeleteKey)
const { isSubmittedFormValid } = useFormValidation()
const { passwordConfirmation, confirmThen } = usePasswordConfirmation()
const { onHttpException, onNetworkError } = useRequestFailure()
const form = useForm({})
const isProcessing = computed(() => passwordConfirmation.processing || form.processing)

// Deleting an account asks for the password first; the server then logs the
// user out and takes them home with a farewell flash message
async function submit(event: Event): Promise<void> {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  await confirmThen(() => {
    form.delete(route('users.destroy', props.username), {
      onHttpException,
      onNetworkError,
      onSuccess: () => {
        isDelete.value = false
      }
    })
  })
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('accounts.delete-account')">
      <po-modal-close @click.prevent="isDelete = false" />
      <v-card-text>
        <p class="mb-2">
          {{ $t('accounts.sorry-see-you-go') }}
          {{ $t('main.proceed-with-caution') }}
          {{ $t('main.action-irreversible') }}
        </p>

        <v-alert color="warning" variant="tonal">
          <p>{{ $t('accounts.delete-account-warning') }}</p>
        </v-alert>

        <v-divider class="mt-3" />

        <v-form id="user-delete-form" @submit.prevent="submit">
          <v-text-field
            v-model="passwordConfirmation.password"
            type="password"
            :label="$t('main.password')"
            :placeholder="$t('main.enter-password-to-continue')"
            :error-messages="passwordConfirmation.errors.password"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <po-button color="primary" type="submit" block :disabled="isProcessing">
            <span v-if="!isProcessing">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
