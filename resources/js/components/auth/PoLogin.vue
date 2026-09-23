<script setup lang="ts">
import { ref, reactive, provide, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import { formDataKey } from '@/composables/keys'
import { useFormErrors } from '@/composables/useFormErrors'
import { useFormSubmit } from '@/composables/useFormSubmit'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useSnackbar } from '@/composables/useSnackbar'
import { PASSWORD_PATTERN, USERNAME_PATTERN } from '@/composables/validationRules'
import type { LaravelValidationErrors } from '@/types/http'

defineOptions({
  layout: PoLayoutLogin
})

type LoginStep = 'guest' | 'checking' | 'login' | 'register'

const LOGIN_FORM = '#login-form'

const socialProviders = [
  { name: 'google', icon: 'fab fa-google', label: 'accounts.continue-with-google' }
]

const { t } = useI18n()
const { validationErrors } = useFormErrors()
const { strNullOrEmpty } = useTypeGuards()
const { setSnackBar } = useSnackbar()
const {
  isPosting: isLoading,
  errors,
  submitForm: postForm
} = useFormSubmit<LaravelValidationErrors>({})
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

function resetForm(): void {
  setTimeout(() => {
    step.value = 'guest'
    clearInputs()
    errors.value = {}
  }, 500)
}

// A failure that carries no field messages (throttled, offline, server error) still has to be shown
function failuresOf(
  error: unknown,
  fallbackField: 'email' | 'password' = 'email'
): LaravelValidationErrors {
  const failures = validationErrors(error)

  return Object.keys(failures).length === 0
    ? { [fallbackField]: [t('main.error-try-again')] }
    : failures
}

async function checkEmail(): Promise<void> {
  await postForm<{ exists: boolean }>({
    formSelector: LOGIN_FORM,
    url: route('email.check'),
    payload: { email: formData.email },
    onSuccess: (data) => {
      step.value = data.exists === true ? 'login' : 'register'
    },
    onError: (error) => failuresOf(error)
  })
}

async function login(): Promise<void> {
  await postForm<{ redirect: string }>({
    formSelector: LOGIN_FORM,
    url: route('login'),
    payload: {
      email: formData.email,
      password: formData.password
    },
    onSuccess: (data) => {
      setSnackBar({
        message: 'accounts.welcome-back',
        color: 'primary',
        active: true
      })

      router.get(data.redirect)
    },
    // Laravel's LoginRequest::authenticate() always keys a failed-login
    // error "email" (deliberately ambiguous about whether the email or
    // the password was wrong). By this point the email is already
    // confirmed to exist (see the checkEmail() step above) and only the
    // password field is visible, so we surface the message there.
    onError: (error) => {
      const failures = failuresOf(error, 'password')

      return { password: failures.email ?? failures.password ?? [] }
    }
  })
}

async function register(): Promise<void> {
  await postForm({
    formSelector: LOGIN_FORM,
    url: route('register'),
    payload: {
      email: formData.email,
      username: formData.username,
      password: formData.password,
      password_confirmation: formData.confirmPassword,
      service_agreement: formData.serviceAgreement,
      privacy_agreement: formData.privacyAgreement
    },
    onSuccess: () => {
      router.get(route('verification.notice'))
    },
    onError: (error) => failuresOf(error)
  })
}

async function submitForm(): Promise<void> {
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
  await postForm({
    formSelector: LOGIN_FORM,
    url: route('password.email'),
    payload: { email: formData.email },
    // The password field is required in this step but is left empty when asking for a reset link
    validate: false,
    onSuccess: () => {
      resetEmailSent.value = true
    },
    onError: (error) => failuresOf(error)
  })
}
</script>

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
          id="login-email"
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
        />

        <template v-if="step === 'register'">
          <v-text-field
            id="register-username"
            v-model="formData.username"
            type="text"
            :label="$t('users.user')"
            :pattern="USERNAME_PATTERN"
            :placeholder="$t('main.enter-your-user')"
            :error-messages="errors.username"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <v-text-field
            id="register-password"
            v-model="formData.password"
            type="password"
            :label="$t('main.password')"
            :pattern="PASSWORD_PATTERN"
            :placeholder="$t('main.enter-your-password')"
            :error-messages="errors.password"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <v-text-field
            id="register-password-confirmation"
            v-model="formData.confirmPassword"
            type="password"
            :label="$t('accounts.confirm-password')"
            :pattern="PASSWORD_PATTERN"
            :placeholder="$t('main.enter-your-password')"
            :error-messages="errors.password"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <po-agreement />
        </template>

        <template v-if="step === 'login'">
          <v-text-field
            id="login-password"
            v-model="formData.password"
            type="password"
            :label="$t('main.password')"
            :placeholder="$t('main.enter-your-password')"
            :error-messages="errors.password"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />
        </template>

        <po-button
          id="login-submit"
          type="submit"
          color="primary"
          size="large"
          block
          :disabled="isLoading"
        >
          <span v-if="!isLoading">{{ $t('main.continue') }}</span>
          <v-progress-circular v-else indeterminate />
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
          <v-icon icon="fas fa-arrow-left" />
        </po-button>
      </v-form>

      <v-alert
        v-if="resetEmailSent || arrivedFromPasswordReset"
        type="success"
        variant="tonal"
        class="mt-5 mx-auto"
        width="85%"
        max-width="600"
      >
        <span v-if="resetEmailSent">{{ $t('accounts.reset-password-link-sent') }}</span>
        <span v-else>{{ $t('accounts.new-password-set') }}</span>
      </v-alert>
    </template>

    <template v-else>
      <div class="po-login d-flex flex-column ga-3">
        <div v-for="provider in socialProviders" :key="provider.name">
          <po-button
            block
            color="primary"
            :href="route('social.login', provider.name)"
            :prepend-icon="provider.icon"
          >
            {{ $t(provider.label) }}
          </po-button>
        </div>

        <div>
          <po-button
            id="login-with-email"
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
