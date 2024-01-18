<script setup>
import { inject } from 'vue'

const loginModal = inject('loginModal')
</script>

<template>
  <v-dialog width="500" class="text-center" persistent>
    <v-card class="pb-10">
      <po-modal-close @click.prevent="loginModal = false"></po-modal-close>

      <v-card-text class="pt-15">
        <po-inline-login :message="$t('accounts.login-to-interact')"></po-inline-login>

        <p class="mt-5 text-caption text-disabled">
          {{ $t('accounts.account-let-you') }}
        </p>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>