<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useRequestFailure } from '@/composables/useRequestFailure'
import { useVerificationCode } from '@/composables/useVerificationCode'

defineProps<{
  pendingEmail: string
}>()

const CODE_LENGTH = 6

// The server swaps the address in and reloads the account once the code matches
const { form, resendOutcome, resendCountdown, verifyCode, resendCode } = useVerificationCode()
const cancellation = useForm({})
const { onHttpException, onNetworkError } = useRequestFailure()

function cancelChange(): void {
  cancellation.delete(route('users.email.cancel'), { onHttpException, onNetworkError })
}
</script>

<template>
  <v-alert color="warning" variant="tonal" class="mt-6" :title="$t('accounts.pending-email-title')">
    <p class="mb-4">{{ $t('accounts.pending-email-hint', { email: pendingEmail }) }}</p>

    <v-otp-input
      v-model="form.code"
      :length="CODE_LENGTH"
      :disabled="form.processing"
      :loading="form.processing"
      :error="form.errors.code !== undefined"
      type="number"
      @finish="verifyCode(route('users.email.verify'))"
    />

    <p v-if="form.errors.code !== undefined" class="po-error text-center text-error ma-0 mb-4">
      {{ form.errors.code }}
    </p>

    <p v-if="resendOutcome === 'success'" class="text-center text-success ma-0 mb-4">
      {{ $t('accounts.verification-code-sent') }}
    </p>

    <p v-if="resendOutcome === 'error'" class="text-center text-error ma-0 mb-4">
      {{ $t('main.error-try-again') }}
    </p>

    <div class="d-flex flex-wrap ga-3">
      <po-button
        color="primary"
        variant="tonal"
        :disabled="resendCountdown > 0"
        @click="resendCode(route('users.email.resend'))"
      >
        {{
          resendCountdown > 0
            ? $t('accounts.resend-code-in', { seconds: resendCountdown })
            : $t('accounts.resend-code')
        }}
      </po-button>

      <po-button
        color="secondary"
        variant="text"
        :disabled="cancellation.processing"
        @click="cancelChange"
      >
        {{ $t('accounts.cancel-email-change') }}
      </po-button>
    </div>
  </v-alert>
</template>
