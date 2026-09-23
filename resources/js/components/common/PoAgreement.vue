<script setup lang="ts">
import { formDataKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useStaticPages } from '@/composables/useStaticPages'

const formData = injectStrict(formDataKey)
const { termsPath, privacyPath } = useStaticPages()
</script>

<template>
  <div class="d-flex align-center">
    <div class="flex-shrink-1">
      <v-switch
        id="agreement-terms"
        v-model="formData.serviceAgreement"
        :label="$t('accounts.i-accept-terms')"
        color="primary"
        required
        hide-details
      />
    </div>

    <div class="ps-4">
      <po-link :href="termsPath()" target="_blank" inertia>
        <v-icon icon="fas fa-arrow-up-right-from-square" size="x-small" />
      </po-link>
    </div>
  </div>

  <div class="d-flex align-center mb-4">
    <div class="flex-shrink-1">
      <v-switch
        id="agreement-privacy"
        v-model="formData.privacyAgreement"
        :label="$t('accounts.i-accept-privacy')"
        color="primary"
        required
        hide-details
      />
    </div>

    <div class="ps-4">
      <po-link :href="privacyPath()" inertia>
        <v-icon icon="fas fa-arrow-up-right-from-square" size="x-small" />
      </po-link>
    </div>
  </div>
</template>
