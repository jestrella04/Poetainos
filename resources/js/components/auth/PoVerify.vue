<script setup lang="ts">
import { ref } from 'vue'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import axios from 'axios'

defineOptions({
  layout: PoLayoutLogin
})

const showSuccess = ref(false)
const showError = ref(false)

function flashMessage(message: 'success' | 'error'): void {
  if (message === 'success') {
    showSuccess.value = true
  } else {
    showError.value = true
  }

  setTimeout(() => {
    if (message === 'success') {
      showSuccess.value = false
    } else {
      showError.value = false
    }
  }, 6000)
}

async function resendLink(): Promise<void> {
  await axios
    .post(route('verification.send'))
    .then(() => {
      flashMessage('success')
    })
    .catch(() => {
      flashMessage('error')
    })
}
</script>

<template>
  <div class="px-10">
    <v-alert
      v-if="showSuccess"
      :text="$t('accounts.verification-link-sent')"
      class="text-caption po-success text-center mb-10"
      color="success"
      variant="tonal"
      rounded
    ></v-alert>

    <v-alert
      v-if="showError"
      :text="$t('main.error-try-again')"
      class="text-caption po-error text-center mb-10"
      color="error"
      variant="tonal"
      rounded
    ></v-alert>

    <p class="text-center text-uppercase font-weight-bold mb-3">
      {{ $t('accounts.verify-email') }}
    </p>

    <p class="text-caption text-justify mb-5">
      {{ $t('accounts.verification-warning-1') }}
      {{ $t('accounts.verification-warning-2') }}
      {{ $t('accounts.verification-warning-3') }}
    </p>

    <po-button color="primary" class="mb-5" block @click="resendLink()">
      {{ $t('accounts.resend-verification') }}
    </po-button>

    <po-button color="secondary" :href="route('home')" variant="text" inertia block>
      {{ $t('accounts.confirmed-continue-ask') }}
    </po-button>
  </div>
</template>
