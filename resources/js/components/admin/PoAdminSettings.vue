<script setup lang="ts">
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import { useFormErrors } from '@/composables/useFormErrors'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { InertiaPageProps } from '@/types/inertia'
import type { LaravelValidationErrors } from '@/types/http'

defineOptions({
  layout: PoLayoutAdmin
})

const { validationErrors } = useFormErrors()
const page = usePage<InertiaPageProps<{ settings: string }>>()
const settings = ref(page.props.settings)
const isPosted = ref(false)
const { isPosting, errors, submitForm: postForm } = useFormSubmit<LaravelValidationErrors>({})

async function submitForm() {
  await postForm({
    formSelector: '#settings-form',
    payload: {
      _method: 'PUT',
      json: settings.value
    },
    cooldown: true,
    onSuccess: () => {
      isPosted.value = true
    },
    onError: validationErrors
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
      />

      <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
        <template v-if="isPosting"><v-progress-circular indeterminate /></template>
        <template v-else>{{ $t('main.save') }}</template>
      </po-button>
    </v-form>

    <v-alert
      v-if="isPosted"
      type="success"
      variant="tonal"
      class="mb-5 mx-auto"
      width="85%"
      max-width="600"
    >
      {{ $t('admin.settings-saved') }}
    </v-alert>
  </po-wrapper>
</template>
