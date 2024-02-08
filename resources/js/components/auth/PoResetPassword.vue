<script setup>
import { defineOptions, ref, reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import axios from 'axios'

defineOptions({
  layout: PoLayoutLogin,
})

const page = computed(() => usePage())
const isLoading = ref(false)
const token = page.value.props.token
const email = page.value.props.email

const formData = reactive({
  password: '',
  confirmPassword: '',
})

const errors = reactive({
  password: [],
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
  const form = document.querySelector('#reset-form')

  if (!form.checkValidity()) {
    form.reportValidity()
    return
  }

  isLoading.value = true
  clearErrors()

  await axios
    .post(window.route('password.store'), {
      token: token,
      email: email,
      password: formData.password,
      password_confirmation: formData.confirmPassword,
    })
    .then(() => {
      router.get(window.route('login', { isReset: 1, isEmail: 1, email: email }))
    })
    .catch((error) => {
      errors.password = error.response.data.errors.password
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<style scoped>
.po-login {
  width: 100%;
  max-width: 400px;
  margin-inline: auto;
  background-color: transparent;
}
</style>

<template>
  <div class="px-10">
    <p class="text-center text-uppercase font-weight-bold mb-3">
      {{ $t('accounts.reset-password') }}
    </p>

    <v-form id="reset-form" class="po-login" @submit.prevent="submitForm()" @reset.prevent="resetForm()">
      <v-text-field v-model="formData.password" type="password" :label="$t('main.password')"
        pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
        :placeholder="$t('main.enter-your-password')" :error-messages="errors.password" persistent-placeholder clearable
        required hide-details="auto">
      </v-text-field>

      <v-text-field v-model="formData.confirmPassword" type="password" :label="$t('accounts.confirm-password')"
        pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
        :placeholder="$t('main.enter-your-password')" :error-messages="errors.password" persistent-placeholder clearable
        hide-details="auto">
      </v-text-field>

      <po-button type="submit" color="primary" size="large" block :disabled="isLoading">
        <span v-if="!isLoading">{{ $t('main.send') }}</span>
        <v-progress-circular v-else indeterminate></v-progress-circular>
      </po-button>
    </v-form>
  </div>
</template>
