<script setup>
import { inject } from 'vue';

const serviceAgreement = inject('serviceAgreement')
const privacyAgreement = inject('privacyAgreement')
</script>

<template>
  <div class="d-flex">
    <div class="flex-grow-1">
      <v-switch v-model="serviceAgreement" :label="$t('accounts.i-accept-terms')" required></v-switch>
    </div>

    <div class="pt-4 ps-4">
      <po-link :href="$route('pages.show', 'condiciones-de-uso')" inertia>
        <v-icon icon="fas fa-arrow-up-right-from-square"></v-icon>
      </po-link>
    </div>
  </div>

  <div class="d-flex">
    <div class="flex-grow-1">
      <v-switch v-model="privacyAgreement" :label="$t('accounts.i-accept-privacy')" required></v-switch>
    </div>

    <div class="pt-4 ps-4">
      <po-link :href="$route('pages.show', 'politicas-de-privacidad')" inertia>
        <v-icon icon="fas fa-arrow-up-right-from-square"></v-icon>
      </po-link>
    </div>
  </div>
</template>