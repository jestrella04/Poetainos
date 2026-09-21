<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import axios from 'axios'
import { useSnackbar } from '@/composables/useSnackbar'
import type { ValidationError } from '@/types/http'

defineOptions({
  layout: PoLayoutLogin
})

const CODE_LENGTH = 6
const RESEND_COOLDOWN_SECONDS = 60
const FLASH_DURATION_MS = 6000

const { t } = useI18n()
const { setSnackBar } = useSnackbar()
const code = ref('')
const isVerifying = ref(false)
const codeError = ref('')
const flash = ref<'success' | 'error' | null>(null)
const resendCountdown = ref(0)

let countdownTimer: ReturnType<typeof setInterval> | undefined
let flashTimer: ReturnType<typeof setTimeout> | undefined

function flashMessage(message: 'success' | 'error'): void {
  flash.value = message
  clearTimeout(flashTimer)
  flashTimer = setTimeout(() => {
    flash.value = null
  }, FLASH_DURATION_MS)
}

function startResendCountdown(): void {
  resendCountdown.value = RESEND_COOLDOWN_SECONDS
  clearInterval(countdownTimer)
  countdownTimer = setInterval(() => {
    resendCountdown.value--

    if (resendCountdown.value <= 0) {
      clearInterval(countdownTimer)
    }
  }, 1000)
}

async function verifyCode(): Promise<void> {
  isVerifying.value = true
  codeError.value = ''

  await axios
    .post<{ url: string }>(route('verification.verify'), { code: code.value })
    .then((response) => {
      setSnackBar({
        message: 'accounts.email-verified',
        color: 'primary',
        active: true
      })

      router.get(response.data.url)
    })
    .catch((error: ValidationError) => {
      code.value = ''
      codeError.value = error.response?.data.errors?.code?.[0] ?? t('main.error-try-again')
    })
    .finally(() => {
      isVerifying.value = false
    })
}

async function resendCode(): Promise<void> {
  await axios
    .post(route('verification.send'))
    .then(() => {
      code.value = ''
      codeError.value = ''
      flashMessage('success')
      startResendCountdown()
    })
    .catch(() => {
      flashMessage('error')
    })
}

onBeforeUnmount(() => {
  clearInterval(countdownTimer)
  clearTimeout(flashTimer)
})
</script>

<template>
  <div class="px-10">
    <v-alert
      v-if="flash === 'success'"
      :text="$t('accounts.verification-code-sent')"
      class="po-success text-center mb-10"
      color="success"
      variant="tonal"
      rounded
    />

    <v-alert
      v-if="flash === 'error'"
      :text="$t('main.error-try-again')"
      class="po-error text-center mb-10"
      color="error"
      variant="tonal"
      rounded
    />

    <p class="text-center text-uppercase font-weight-bold mb-3">
      {{ $t('accounts.verify-email') }}
    </p>

    <p class="text-justify mb-4">
      {{ $t('accounts.verification-warning-1') }}
      {{ $t('accounts.verification-warning-2') }}
      {{ $t('accounts.verification-warning-3') }}
    </p>

    <v-otp-input
      v-model="code"
      :length="CODE_LENGTH"
      :disabled="isVerifying"
      :loading="isVerifying"
      :error="codeError !== ''"
      type="number"
      autofocus
      @finish="verifyCode()"
    />

    <p v-if="codeError !== ''" class="po-error text-center text-error ma-0 mb-4">
      {{ codeError }}
    </p>

    <po-button
      color="primary"
      class="mt-5 mb-5"
      :disabled="resendCountdown > 0"
      block
      @click="resendCode()"
    >
      {{
        resendCountdown > 0
          ? $t('accounts.resend-code-in', { seconds: resendCountdown })
          : $t('accounts.resend-code')
      }}
    </po-button>

    <po-button color="secondary" :href="route('home')" variant="text" inertia block>
      {{ $t('accounts.skip-verification-continue') }}
    </po-button>
  </div>
</template>
