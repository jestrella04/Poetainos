<script setup lang="ts">
import { loginModalKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'

const loginModal = injectStrict(loginModalKey)
</script>

<template>
  <v-dialog width="500" class="text-center" persistent>
    <v-card class="pb-10">
      <po-modal-close @click.prevent="loginModal = false" />

      <v-card-text class="pt-15">
        <po-inline-login :message="$t('accounts.login-to-interact')" />

        <p class="mt-5 text-disabled">
          {{ $t('accounts.account-let-you') }}
        </p>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
