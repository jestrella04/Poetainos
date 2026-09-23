<script setup lang="ts">
import { reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import { useFormErrors } from '@/composables/useFormErrors'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { InertiaPageProps } from '@/types/inertia'
import type { LaravelValidationErrors } from '@/types/http'

defineOptions({
  layout: PoLayoutLogin
})

const page = usePage<InertiaPageProps<{ token: string; email: string }>>()
const { validationErrors } = useFormErrors()
const {
  isPosting: isLoading,
  errors,
  submitForm: postForm
} = useFormSubmit<LaravelValidationErrors>({})
const token = page.props.token
const email = page.props.email

const formData = reactive({
  password: '',
  confirmPassword: ''
})

function clearInputs() {
  formData.password = ''
  formData.confirmPassword = ''
}

function resetForm() {
  setTimeout(() => {
    clearInputs()
    errors.value = {}
  }, 500)
}

async function submitForm() {
  await postForm({
    formSelector: '#reset-form',
    url: route('password.store'),
    payload: {
      token: token,
      email: email,
      password: formData.password,
      password_confirmation: formData.confirmPassword
    },
    onSuccess: () => {
      router.get(route('login', { isReset: 1, isEmail: 1, email: email }))
    },
    onError: validationErrors
  })
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

    <v-form
      id="reset-form"
      class="po-login"
      @submit.prevent="submitForm()"
      @reset.prevent="resetForm()"
    >
      <v-text-field
        v-model="formData.password"
        type="password"
        :label="$t('main.password')"
        pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
        :placeholder="$t('main.enter-your-password')"
        :error-messages="errors.password"
        persistent-placeholder
        clearable
        required
        hide-details="auto"
      />

      <v-text-field
        v-model="formData.confirmPassword"
        type="password"
        :label="$t('accounts.confirm-password')"
        pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
        :placeholder="$t('main.enter-your-password')"
        :error-messages="errors.password"
        persistent-placeholder
        clearable
        hide-details="auto"
      />

      <po-button type="submit" color="primary" size="large" block :disabled="isLoading">
        <span v-if="!isLoading">{{ $t('main.send') }}</span>
        <v-progress-circular v-else indeterminate />
      </po-button>
    </v-form>
  </div>
</template>
