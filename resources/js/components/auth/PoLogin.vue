<script setup>
import { defineOptions, ref, reactive, provide, inject } from 'vue'
import { router } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import axios from 'axios'
import { onMounted } from 'vue';

defineOptions({
  layout: PoLayoutLogin,
})

const helper = inject('helper')
const isLoading = ref(false)
const isEmail = ref(false)
const shouldLogin = ref(false)
const shouldRegister = ref(false)

const formData = reactive({
  email: '',
  username: '',
  password: '',
  confirmPassword: '',
  serviceAgreement: false,
  privacyAgreement: false
})

const errors = reactive({
  email: [],
  username: [],
  password: [],
})

provide('formData', formData)

onMounted(() => {
  const params = new URLSearchParams(window.location.search)

  if ('1' === params.get('isEmail')) {
    isEmail.value = true
  }
})

function clearInputs() {
  formData.email = ''
  formData.username = ''
  formData.password = ''
  formData.confirmPassword = ''
  formData.serviceAgreement = false
  formData.privacyAgreement = false
}

function clearErrors() {
  errors.email = []
  errors.username = []
  errors.password = []
}

function resetForm() {
  setTimeout(() => {
    isEmail.value = false
    shouldLogin.value = false
    shouldRegister.value = false

    // Clear inputs
    clearInputs()

    // Clear errors
    clearErrors()
  }, 500)
}

async function submitForm() {
  const form = document.querySelector('#login-form')

  if (!form.checkValidity()) {
    form.reportValidity()
    return
  }

  // Check if email exists
  if (!shouldLogin.value && !shouldRegister.value) {
    isLoading.value = true

    await axios
      .post(window.route('email.check'), { email: formData.email })
      .then((response) => {
        if (response.data.exists) {
          shouldLogin.value = true
          shouldRegister.value = false
        } else {
          shouldLogin.value = false
          shouldRegister.value = true
        }
      })
      .catch((error) => {
        console.log(error)
      })
      .finally(() => {
        isLoading.value = false
      })

    // Prevent entering following if statement
    return
  }

  // Attempt to login
  else if (shouldLogin.value) {
    isLoading.value = true
    clearErrors()

    await axios
      .post(window.route('login'), { email: formData.email, password: formData.password })
      .then((response) => {
        helper.setSnackBar({
          message: 'accounts.welcome-back',
          color: 'primary',
          active: true
        })

        router.get(response.data.redirect)
      })
      .catch((error) => {
        errors.password = error.response.data.errors.email // Intentional
      })
      .finally(() => {
        isLoading.value = false
      })

    // Prevent entering following if statement
    return
  }

  // Attempt to register
  else if (shouldRegister.value) {
    isLoading.value = true
    clearErrors()

    await axios
      .post(window.route('register'), {
        email: formData.email,
        username: formData.username,
        password: formData.password,
        password_confirmation: formData.confirmPassword,
        service_agreement: formData.serviceAgreement,
        privacy_agreement: formData.privacyAgreement
      })
      .then(() => {
        router.get(window.route('verification.notice'))
      })
      .catch((error) => {
        errors.email = error.response.data.errors.email
        errors.username = error.response.data.errors.username
        errors.password = error.response.data.errors.password
      })
      .finally(() => {
        isLoading.value = false
      })

    // Prevent entering following if statement
    return
  }
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
      {{ $t('accounts.welcome-to-hood') }}
    </p>

    <template v-if="isEmail">
      <v-form id="login-form" class="po-login" @submit.prevent="submitForm()" @reset.prevent="resetForm()">
        <v-text-field v-model="formData.email" type="email" :label="$t('main.email')"
          :placeholder="$t('main.enter-your-email')" :error-messages="errors.email"
          :readonly="shouldLogin || shouldRegister" persistent-placeholder :clearable="!shouldLogin && !shouldRegister"
          required hide-details="auto">
        </v-text-field>

        <template v-if="shouldRegister">
          <v-text-field v-model="formData.username" type="text" :label="$t('users.user')"
            pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$" :placeholder="$t('main.enter-your-user')"
            :error-messages="errors.username" persistent-placeholder clearable required hide-details="auto">
          </v-text-field>

          <v-text-field v-model="formData.password" type="password" :label="$t('main.password')"
            pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
            :placeholder="$t('main.enter-your-password')" :error-messages="errors.password" persistent-placeholder
            clearable required hide-details="auto">
          </v-text-field>

          <v-text-field v-model="formData.confirmPassword" type="password" :label="$t('accounts.confirm-password')"
            pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
            :placeholder="$t('main.enter-your-password')" :error-messages="errors.password" persistent-placeholder
            clearable hide-details="auto">
          </v-text-field>

          <po-agreement></po-agreement>
        </template>

        <template v-if="shouldLogin">
          <v-text-field v-model="formData.password" type="password" :label="$t('main.password')"
            :placeholder="$t('main.enter-your-password')" :error-messages="errors.password" persistent-placeholder
            clearable required hide-details="auto">
          </v-text-field>
        </template>

        <po-button type="submit" color="primary" size="large" block :disabled="isLoading">
          <span v-if="!isLoading">{{ $t('main.continue') }}</span>
          <v-progress-circular v-else indeterminate></v-progress-circular>
        </po-button>

        <po-button type="reset" color="secondary" size="large" variant="text" class="mt-5" block>
          <v-icon icon="fas fa-arrow-left"></v-icon>
        </po-button>
      </v-form>
    </template>

    <template v-else>
      <v-list class="po-login">
        <v-list-item>
          <po-button block color="primary" :href="$route('social.login', 'facebook')" prepend-icon="fab fa-facebook-f">
            {{ $t('accounts.continue-with-facebook') }}
          </po-button>
        </v-list-item>

        <v-list-item>
          <po-button block color="primary" :href="$route('social.login', 'twitter')" prepend-icon="fab fa-x-twitter">
            {{ $t('accounts.continue-with-x-twitter') }}
          </po-button>
        </v-list-item>

        <v-list-item>
          <po-button block color="primary" :href="$route('social.login', 'google')" prepend-icon="fab fa-google">
            {{ $t('accounts.continue-with-google') }}
          </po-button>
        </v-list-item>

        <v-list-item>
          <po-button block color="primary" prepend-icon="fas fa-at" @click.prevent="isEmail = true">
            {{ $t('accounts.continue-with-email') }}
          </po-button>
        </v-list-item>

        <v-list-item>
          <po-button block color="primary" :href="$route('home')" prepend-icon="fas fa-ghost"
            @click.prevent="$inertia.get($route('home'))">
            {{ $t('accounts.continue-as-guest') }}
          </po-button>
        </v-list-item>
      </v-list>
    </template>
  </div>
</template>
