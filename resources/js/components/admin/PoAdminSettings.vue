<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import axios from 'axios'
import { useFormValidation } from '@/composables/useFormValidation'
import type { InertiaPageProps } from '@/types/inertia'
import type { LaravelValidationErrors, ValidationError } from '@/types/http'

defineOptions({
  layout: PoLayoutAdmin
})

const { checkFormValidity } = useFormValidation()
const page = computed(() => usePage<InertiaPageProps<{ settings: string }>>())
const settings = ref(page.value.props.settings)
const isPosting = ref(false)
const isPosted = ref(false)
const errors = ref<LaravelValidationErrors>({})

function submitForm() {
  const form = document.querySelector<HTMLFormElement>('#settings-form')

  if (!form || !checkFormValidity(form)) {
    return
  }

  isPosting.value = true

  void axios
    .post(form.action, {
      _method: 'PUT',
      json: settings.value
    })
    .then(() => {
      isPosted.value = true
    })
    .catch((error: ValidationError) => {
      errors.value = error.response?.data.errors ?? {}
    })
    .finally(() => {
      setTimeout(() => {
        isPosting.value = false
      }, 1000)
    })
}
</script>

<template>
  <po-wrapper>
    <v-card-title>{{ $t('admin.settings') }}</v-card-title>

    <v-form
      id="settings-form"
      :action="route('admin.settings.edit')"
      class="mb-5"
      @submit.prevent="submitForm"
    >
      <v-textarea
        v-model="settings"
        :label="$t('admin.settings')"
        rows="20"
        :hint="$t('admin.settings-warning')"
        hide-details="auto"
        :error-messages="errors.json"
        persistent-hint
        required
      ></v-textarea>

      <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
        <template v-if="isPosting"
          ><v-progress-circular indeterminate></v-progress-circular
        ></template>
        <template v-else>{{ $t('main.save') }}</template>
      </po-button>
    </v-form>

    <v-alert
      v-if="isPosted"
      type="success"
      variant="tonal"
      class="mb-5 mx-auto"
      style="width: 85%; max-width: 600px"
    >
      {{ $t('admin.settings-saved') }}
    </v-alert>
  </po-wrapper>
</template>
