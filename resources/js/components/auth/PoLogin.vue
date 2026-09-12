<script setup lang="ts">
import { ref, reactive, provide, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import axios from 'axios'
import { formDataKey } from '@/composables/keys'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useSnackbar } from '@/composables/useSnackbar'
import type { ValidationError } from '@/types/http'

defineOptions({
  layout: PoLayoutLogin
})

type LoginStep = 'guest' | 'checking' | 'login' | 'register'

const { strNullOrEmpty } = useTypeGuards()
const { setSnackBar } = useSnackbar()
const isLoading = ref(false)
const step = ref<LoginStep>('guest')
const arrivedFromPasswordReset = ref(false)
const resetEmailSent = ref(false)

const formData = reactive({
  email: '',
  username: '',
  password: '',
  confirmPassword: '',
  serviceAgreement: false,
  privacyAgreement: false
})

const errors = reactive<{ email: string[]; username: string[]; password: string[] }>({
  email: [],
  username: [],
  password: []
})

provide(formDataKey, formData)

onMounted(() => {
  const params = new URLSearchParams(window.location.search)

  if ('1' === params.get('isEmail')) {
    step.value = 'checking'

    const email = params.get('email')

    if (email !== null && !strNullOrEmpty(email)) {
      formData.email = email
      step.value = 'login'
    }
  }

  if ('1' === params.get('isReset')) {
    arrivedFromPasswordReset.value = true
  }
})

function clearInputs(): void {
  formData.email = ''
  formData.username = ''
  formData.password = ''
  formData.confirmPassword = ''
  formData.serviceAgreement = false
  formData.privacyAgreement = false
}

function clearErrors(): void {
  errors.email = []
  errors.username = []
  errors.password = []
}

function resetForm(): void {
  setTimeout(() => {
    step.value = 'guest'
    clearInputs()
    clearErrors()
  }, 500)
}

async function checkEmail(): Promise<void> {
  isLoading.value = true

  await axios
    .post<{ exists: boolean }>(route('email.check'), { email: formData.email })
    .then((response) => {
      step.value = response.data.exists === true ? 'login' : 'register'
    })
    .catch((error: unknown) => {
      console.log(error)
    })
    .finally(() => {
      isLoading.value = false
    })
}

async function login(): Promise<void> {
  isLoading.value = true
  clearErrors()

  await axios
    .post<{ redirect: string }>(route('login'), {
      email: formData.email,
      password: formData.password
    })
    .then((response) => {
      setSnackBar({
        message: 'accounts.welcome-back',
        color: 'primary',
        active: true
      })

      router.get(response.data.redirect)
    })
    .catch((error: ValidationError) => {
      // Laravel's LoginRequest::authenticate() always keys a failed-login
      // error "email" (deliberately ambiguous about whether the email or
      // the password was wrong). By this point the email is already
      // confirmed to exist (see the checkEmail() step above) and only the
      // password field is visible, so we surface the message there.
      errors.password = error.response?.data.errors.email ?? []
    })
    .finally(() => {
      isLoading.value = false
    })
}

async function register(): Promise<void> {
  isLoading.value = true
  clearErrors()

  await axios
    .post(route('register'), {
      email: formData.email,
      username: formData.username,
      password: formData.password,
      password_confirmation: formData.confirmPassword,
      service_agreement: formData.serviceAgreement,
      privacy_agreement: formData.privacyAgreement
    })
    .then(() => {
      router.get(route('verification.notice'))
    })
    .catch((error: ValidationError) => {
      errors.email = error.response?.data.errors.email ?? []
      errors.username = error.response?.data.errors.username ?? []
      errors.password = error.response?.data.errors.password ?? []
    })
    .finally(() => {
      isLoading.value = false
    })
}

async function submitForm(): Promise<void> {
  const form = document.querySelector<HTMLFormElement>('#login-form')

  if (form === null) {
    return
  }

  if (form.checkValidity() === false) {
    form.reportValidity()
    return
  }

  switch (step.value) {
    case 'checking':
      await checkEmail()
      break

    case 'login':
      await login()
      break

    case 'register':
      await register()
      break
  }
}

async function resetPassword(): Promise<void> {
  await axios
    .post(route('password.email'), { email: formData.email })
    .then(() => {
      resetEmailSent.value = true
    })
    .catch((error: ValidationError) => {
      errors.email = error.response?.data.errors.email ?? []
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
      {{ $t('accounts.welcome-to-hood') }}
    </p>

    <template v-if="step !== 'guest'">
      <v-form
        id="login-form"
        class="po-login"
        @submit.prevent="submitForm()"
        @reset.prevent="resetForm()"
      >
        <v-text-field
          v-model="formData.email"
          type="email"
          :label="$t('main.email')"
          :placeholder="$t('main.enter-your-email')"
          :error-messages="errors.email"
          :readonly="step === 'login' || step === 'register'"
          persistent-placeholder
          :clearable="step === 'checking'"
          required
          hide-details="auto"
        >
        </v-text-field>

        <template v-if="step === 'register'">
          <v-text-field
            v-model="formData.username"
            type="text"
            :label="$t('users.user')"
            pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
            :placeholder="$t('main.enter-your-user')"
            :error-messages="errors.username"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          >
          </v-text-field>

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
          >
          </v-text-field>

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
          >
          </v-text-field>

          <po-agreement></po-agreement>
        </template>

        <template v-if="step === 'login'">
          <v-text-field
            v-model="formData.password"
            type="password"
            :label="$t('main.password')"
            :placeholder="$t('main.enter-your-password')"
            :error-messages="errors.password"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          >
          </v-text-field>
        </template>

        <po-button type="submit" color="primary" size="large" block :disabled="isLoading">
          <span v-if="!isLoading">{{ $t('main.continue') }}</span>
          <v-progress-circular v-else indeterminate></v-progress-circular>
        </po-button>

        <po-button
          v-if="step === 'login'"
          size="x-small"
          variant="plain"
          class="mt-5"
          block
          @click.prevent="resetPassword"
        >
          {{ $t('accounts.forgot-password-ask') }}
        </po-button>

        <po-button type="reset" size="large" variant="plain" class="mt-5" block>
          <v-icon icon="fas fa-arrow-left"></v-icon>
        </po-button>
      </v-form>

      <v-alert
        v-if="resetEmailSent || arrivedFromPasswordReset"
        type="success"
        variant="tonal"
        class="mt-5 mx-auto text-caption"
        style="width: 85%; max-width: 600px"
      >
        <span v-if="resetEmailSent">{{ $t('accounts.reset-password-link-sent') }}</span>
        <span v-else>{{ $t('accounts.new-password-set') }}</span>
      </v-alert>
    </template>

    <template v-else>
      <div class="po-login d-flex flex-column ga-3">
        <div>
          <po-button
            block
            color="primary"
            :href="route('social.login', 'facebook')"
            prepend-icon="fab fa-facebook-f"
          >
            {{ $t('accounts.continue-with-facebook') }}
          </po-button>
        </div>

        <div>
          <po-button
            block
            color="primary"
            :href="route('social.login', 'twitter')"
            prepend-icon="fab fa-x-twitter"
          >
            {{ $t('accounts.continue-with-x-twitter') }}
          </po-button>
        </div>

        <div>
          <po-button
            block
            color="primary"
            :href="route('social.login', 'google')"
            prepend-icon="fab fa-google"
          >
            {{ $t('accounts.continue-with-google') }}
          </po-button>
        </div>

        <div>
          <po-button
            block
            color="primary"
            prepend-icon="fas fa-at"
            @click.prevent="step = 'checking'"
          >
            {{ $t('accounts.continue-with-email') }}
          </po-button>
        </div>

        <div>
          <po-button
            block
            color="primary"
            :href="route('home')"
            prepend-icon="fas fa-ghost"
            @click.prevent="$inertia.get(route('home'))"
          >
            {{ $t('accounts.continue-as-guest') }}
          </po-button>
        </div>
      </div>
    </template>
  </div>
</template>
