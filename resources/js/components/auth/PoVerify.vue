<script setup>
import { defineOptions } from 'vue'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import axios from 'axios'

defineOptions({
  layout: PoLayoutLogin
})

async function resendLink() {
  await axios
    .post(route('verification.send'))
    .then(() => {
      const alert = document.querySelector('.po-success')
      alert.classList.remove('d-none')

      setTimeout(() => {
        alert.classList.add('d-none')
      }, 6000)
    })
    .catch(() => {
      const alert = document.querySelector('.po-error')
      alert.classList.remove('d-none')

      setTimeout(() => {
        alert.classList.add('d-none')
      }, 6000)
    })
    .finally()
}
</script>

<template>
  <div class="px-10">
    <v-alert
      :text="$t('accounts.verification-link-sent')"
      class="text-caption po-success text-center mb-10 d-none"
      color="success"
      variant="tonal"
      rounded
    ></v-alert>

    <v-alert
      :text="$t('main.error-try-again')"
      class="text-caption po-error text-center mb-10 d-none"
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
