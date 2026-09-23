<script setup lang="ts">
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { forceSnackBarKey, isDeleteKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useFormErrors } from '@/composables/useFormErrors'
import { useSnackbar } from '@/composables/useSnackbar'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { LaravelValidationErrors } from '@/types/http'

defineProps<{
  username: string
}>()

const { validationErrors } = useFormErrors()
const { setSnackBar } = useSnackbar()
const isDelete = injectStrict(isDeleteKey)
const forceSnackBar = injectStrict(forceSnackBarKey)
const formData = reactive({
  password: ''
})
const { isPosting, errors, submitForm } = useFormSubmit<LaravelValidationErrors>({})

async function submit(): Promise<void> {
  await submitForm({
    formSelector: '#user-delete-form',
    payload: { _method: 'DELETE' },
    preSubmit: async () => {
      await axios.post(route('password.confirmer'), { password: formData.password })
    },
    onSuccess: () => {
      router.visit(route('home'))
      setSnackBar({
        message: 'accounts.account-deleted',
        color: 'success',
        active: true
      })

      forceSnackBar.value = true
      isDelete.value = false
    },
    onError: validationErrors
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

        <v-form
          id="user-delete-form"
          :action="route('users.destroy', username)"
          @submit.prevent="submit"
        >
          <v-text-field
            v-model="formData.password"
            type="password"
            :label="$t('main.password')"
            :placeholder="$t('main.enter-password-to-continue')"
            :error-messages="errors.password"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <po-button color="primary" type="submit" block :disabled="isPosting">
            <span v-if="!isPosting">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
