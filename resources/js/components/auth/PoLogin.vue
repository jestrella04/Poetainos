<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useForm, useHttp, usePage } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'
import { PASSWORD_PATTERN, USERNAME_PATTERN } from '@/composables/validationRules'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutLogin
})

type LoginStep = 'guest' | 'checking' | 'login' | 'register'

const RESET_DELAY_MS = 500

const socialProviders = [
  { name: 'google', icon: 'fab fa-google', label: 'accounts.continue-with-google' }
]

// The address a password was just reset for, when arriving from the reset form
const page = usePage<InertiaPageProps<{ email?: string | null }>>()
const { isBlank } = useTypeGuards()
const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError, whenSettled } = useRequestFailure()
const step = ref<LoginStep>('guest')
const arrivedFromPasswordReset = ref(false)
const resetEmailSent = ref(false)

const form = useForm({
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
  service_agreement: false,
  privacy_agreement: false
})
const emailCheck = useHttp<{ email: string }, { exists: boolean }>({ email: '' })
const isLoading = computed(() => form.processing || emailCheck.processing)

// A failed sign-in is reported on "email" on purpose, not to tell whether the
// email or the password was wrong; by then the email is known to exist and only
// the password field is editable, so the message is shown there.
const emailError = computed(() =>
  step.value === 'login' ? undefined : (emailCheck.errors.email ?? form.errors.email)
)
const passwordError = computed(() =>
  step.value === 'login' ? (form.errors.email ?? form.errors.password) : form.errors.password
)

onMounted(() => {
  const params = new URLSearchParams(window.location.search)

  if (params.get('isEmail') === '1') {
    step.value = 'checking'

    const email = page.props.email ?? null

    if (email !== null && !isBlank(email)) {
      form.email = email
      step.value = 'login'
    }
  }

  if (params.get('isReset') === '1') {
    arrivedFromPasswordReset.value = true
  }
})

function resetForm(): void {
  setTimeout(() => {
    step.value = 'guest'
    form.reset()
    form.clearErrors()
    emailCheck.clearErrors()
  }, RESET_DELAY_MS)
}

async function checkEmail(): Promise<void> {
  await whenSettled(
    emailCheck
      .transform(() => ({ email: form.email }))
      .post(route('email.check'), {
        onHttpException,
        onNetworkError,
        onSuccess: (data) => {
          step.value = data.exists === true ? 'login' : 'register'
        }
      })
  )
}

function submitForm(event: Event): void {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  switch (step.value) {
    case 'checking':
      void checkEmail()
      break

    case 'login':
      // The server redirects to where the user was headed, flashing the welcome back
      form
        .transform((data) => ({ email: data.email, password: data.password }))
        .post(route('login'), { onHttpException, onNetworkError })
      break

    case 'register':
      form.transform((data) => data).post(route('register'), { onHttpException, onNetworkError })
      break
  }
}

function resetPassword(): void {
  // The password field is required in this step but is left empty when asking for a reset link
  form
    .transform((data) => ({ email: data.email }))
    .post(route('password.email'), {
      onHttpException,
      onNetworkError,
      onSuccess: () => {
        resetEmailSent.value = true
      }
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
        @submit.prevent="submitForm"
        @reset.prevent="resetForm()"
      >
        <v-text-field
          id="login-email"
          v-model="form.email"
          type="email"
          :label="$t('main.email')"
          :placeholder="$t('main.enter-your-email')"
          :error-messages="emailError"
          :readonly="step === 'login' || step === 'register'"
          persistent-placeholder
          :clearable="step === 'checking'"
          required
          hide-details="auto"
        />

        <template v-if="step === 'register'">
          <v-text-field
            id="register-username"
            v-model="form.username"
            type="text"
            :label="$t('users.user')"
            :pattern="USERNAME_PATTERN"
            :placeholder="$t('main.enter-your-user')"
            :error-messages="form.errors.username"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <v-text-field
            id="register-password"
            v-model="form.password"
            type="password"
            :label="$t('main.password')"
            :pattern="PASSWORD_PATTERN"
            :placeholder="$t('main.enter-your-password')"
            :error-messages="passwordError"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <v-text-field
            id="register-password-confirmation"
            v-model="form.password_confirmation"
            type="password"
            :label="$t('accounts.confirm-password')"
            :pattern="PASSWORD_PATTERN"
            :placeholder="$t('main.enter-your-password')"
            :error-messages="passwordError"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <po-agreement
            v-model:service-agreement="form.service_agreement"
            v-model:privacy-agreement="form.privacy_agreement"
          />
        </template>

        <template v-if="step === 'login'">
          <v-text-field
            id="login-password"
            v-model="form.password"
            type="password"
            :label="$t('main.password')"
            :placeholder="$t('main.enter-your-password')"
            :error-messages="passwordError"
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
