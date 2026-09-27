<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'
import { PASSWORD_PATTERN } from '@/composables/validationRules'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutLogin
})

const page = usePage<InertiaPageProps<{ token: string; email: string }>>()
const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError } = useRequestFailure()

const form = useForm({
  token: page.props.token,
  email: page.props.email,
  password: '',
  password_confirmation: ''
})

// The server sends the user back to sign in with the new password
function submitForm(event: Event): void {
  if (isSubmittedFormValid(event) === true) {
    form.post(route('password.store'), { onHttpException, onNetworkError })
  }
}
</script>

<style scoped>
/* Caps the form to a comfortable width and centers it; Vuetify's v-form has no width preset. */
.po-login {
  width: 100%;
  max-width: 400px;
  margin-inline: auto;
}
</style>

<template>
  <div class="px-10">
    <p class="text-center text-uppercase font-weight-bold mb-3">
      {{ $t('accounts.reset-password') }}
    </p>

    <v-form id="reset-form" class="po-login" @submit.prevent="submitForm">
      <v-text-field
        v-model="form.password"
        type="password"
        :label="$t('main.password')"
        :pattern="PASSWORD_PATTERN"
        :placeholder="$t('main.enter-your-password')"
        :error-messages="form.errors.password ?? form.errors.email"
        persistent-placeholder
        clearable
        required
        hide-details="auto"
      />

      <v-text-field
        v-model="form.password_confirmation"
        type="password"
        :label="$t('accounts.confirm-password')"
        :pattern="PASSWORD_PATTERN"
        :placeholder="$t('main.enter-your-password')"
        :error-messages="form.errors.password ?? form.errors.email"
        persistent-placeholder
        clearable
        hide-details="auto"
      />

      <po-button type="submit" color="primary" size="large" block :disabled="form.processing">
        <span v-if="!form.processing">{{ $t('main.send') }}</span>
        <v-progress-circular v-else indeterminate />
      </po-button>
    </v-form>
  </div>
</template>
