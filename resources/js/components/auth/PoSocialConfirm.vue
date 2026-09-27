<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import { useVerificationCode } from '@/composables/useVerificationCode'

defineOptions({
  layout: PoLayoutLogin
})

const props = defineProps<{
  service: string
  email: string
}>()

const CODE_LENGTH = 6

const page = usePage()
// The server signs the user in and redirects onward once the code matches, flashing the welcome
const { form, resendOutcome, resendCountdown, verifyCode, resendCode } = useVerificationCode()
const providerName = computed(() => props.service.charAt(0).toUpperCase() + props.service.slice(1))
</script>

<template>
  <div class="px-10">
    <v-alert
      v-if="resendOutcome === 'success'"
      :text="$t('accounts.verification-code-sent')"
      class="po-success text-center mb-10"
      color="success"
      variant="tonal"
      rounded
    />

    <v-alert
      v-if="resendOutcome === 'error'"
      :text="$t('main.error-try-again')"
      class="po-error text-center mb-10"
      color="error"
      variant="tonal"
      rounded
    />

    <p class="text-center text-uppercase font-weight-bold mb-3">
      {{ $t('accounts.social-confirm-title') }}
    </p>

    <v-alert
      :text="
        $t('accounts.social-confirm-first-time', {
          provider: providerName,
          site: page.props.site.name,
          email: props.email
        })
      "
      class="mb-4"
      color="primary"
      variant="tonal"
      rounded
    />

    <p class="text-justify mb-4">
      {{ $t('accounts.verification-warning-2') }}
    </p>

    <v-otp-input
      v-model="form.code"
      :length="CODE_LENGTH"
      :disabled="form.processing"
      :loading="form.processing"
      :error="form.errors.code !== undefined"
      type="number"
      autofocus
      @finish="verifyCode(route('social.confirm.verify', props.service))"
    />

    <p v-if="form.errors.code !== undefined" class="po-error text-center text-error ma-0 mb-4">
      {{ form.errors.code }}
    </p>

    <po-button
      color="primary"
      class="mt-5 mb-5"
      :disabled="resendCountdown > 0"
      block
      @click="resendCode(route('social.confirm.resend', props.service))"
    >
      {{
        resendCountdown > 0
          ? $t('accounts.resend-code-in', { seconds: resendCountdown })
          : $t('accounts.resend-code')
      }}
    </po-button>

    <po-button color="secondary" :href="route('login')" variant="text" inertia block>
      {{ $t('accounts.back-to-login') }}
    </po-button>
  </div>
</template>
