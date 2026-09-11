<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { forceSnackBarKey, isDeleteKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useFormValidation } from '@/composables/useFormValidation'
import { useSnackbar } from '@/composables/useSnackbar'
import type { LaravelValidationErrors, ValidationError } from '@/types/http'

defineProps<{
  username: string
}>()

const { checkFormValidity } = useFormValidation()
const { setSnackBar } = useSnackbar()
const isDelete = injectStrict(isDeleteKey)
const isPosting = ref(false)
const errors = ref<LaravelValidationErrors>({})
const forceSnackBar = injectStrict(forceSnackBarKey)
const formData = reactive({
  password: ''
})

async function submit() {
  const form = document.querySelector<HTMLFormElement>('#user-delete-form')

  if (!form || !checkFormValidity(form)) {
    return
  }

  isPosting.value = true
  errors.value = {}

  await axios
    .post(route('password.confirmer'), {
      //'_method': 'DELETE',
      password: formData.password
    })
    .then(() => {
      void axios
        .post(form.action, {
          _method: 'DELETE'
        })
        .then(() => {
          router.visit(route('home'))
          setSnackBar({
            message: 'accounts.account-deleted',
            color: 'success',
            active: true
          })

          forceSnackBar.value = true
          isDelete.value = false
        })
    })
    .catch((error: ValidationError) => {
      errors.value = error.response?.data.errors ?? {}
    })
    .finally(() => {
      isPosting.value = false
    })
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('accounts.delete-account')">
      <po-modal-close @click.prevent="isDelete = false"></po-modal-close>
      <v-card-text>
        <p class="mb-2">
          {{ $t('accounts.sorry-see-you-go') }}
          {{ $t('main.proceed-with-caution') }}
          {{ $t('main.action-irreversible') }}
        </p>

        <v-alert color="warning" variant="tonal">
          <p>{{ $t('accounts.delete-account-warning') }}</p>
        </v-alert>

        <v-divider class="mt-3"></v-divider>

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
          >
          </v-text-field>

          <po-button color="primary" type="submit" block :disabled="isPosting">
            <span v-if="!isPosting">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate></v-progress-circular>
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
