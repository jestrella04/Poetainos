<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import axios from 'axios'
import type { InertiaPageProps } from '@/types/inertia'
import type { ValidationError } from '@/types/http'

defineOptions({
  layout: PoLayoutLogin
})

const page = usePage<InertiaPageProps<{ token: string; email: string }>>()
const isLoading = ref(false)
const token = page.props.token
const email = page.props.email

const formData = reactive({
  password: '',
  confirmPassword: ''
})

const errors = reactive<{ password: string[] }>({
  password: []
})

function clearInputs() {
  formData.password = ''
  formData.confirmPassword = ''
}

function clearErrors() {
  errors.password = []
}

function resetForm() {
  setTimeout(() => {
    // Clear inputs
    clearInputs()

    // Clear errors
    clearErrors()
  }, 500)
}

async function submitForm() {
  const form = document.querySelector<HTMLFormElement>('#reset-form')

  if (!form) {
    return
  }

  if (!form.checkValidity()) {
    form.reportValidity()
    return
  }

  isLoading.value = true
  clearErrors()

  await axios
    .post(route('password.store'), {
      token: token,
      email: email,
      password: formData.password,
      password_confirmation: formData.confirmPassword
    })
    .then(() => {
      router.get(route('login', { isReset: 1, isEmail: 1, email: email }))
    })
    .catch((error: ValidationError) => {
      errors.password = error.response?.data.errors.password ?? []
    })
    .finally(() => {
      isLoading.value = false
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
