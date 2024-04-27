<script setup>
import { inject } from 'vue'

const formData = inject('formData')
</script>

<template>
  <div class="d-flex">
    <div class="flex-shrink-1">
      <v-switch v-model="formData.serviceAgreement" :label="$t('accounts.i-accept-terms')" color="primary" class="mb-0"
        required hide-details></v-switch>
    </div>

    <div class="pt-4 ps-4">
      <po-link :href="route('pages.show', 'condiciones-de-uso')" target="_blank" inertia>
        <v-icon icon="fas fa-arrow-up-right-from-square"></v-icon>
      </po-link>
    </div>
  </div>

  <div class="d-flex">
    <div class="flex-shrink-1">
      <v-switch v-model="formData.privacyAgreement" :label="$t('accounts.i-accept-privacy')" color="primary" required
        hide-details></v-switch>
    </div>

    <div class="pt-4 ps-4">
      <po-link :href="route('pages.show', 'politicas-de-privacidad')" inertia>
        <v-icon icon="fas fa-arrow-up-right-from-square"></v-icon>
      </po-link>
    </div>
  </div>
</template>