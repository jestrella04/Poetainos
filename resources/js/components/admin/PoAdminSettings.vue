<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import PoAdminTitle from './partials/PoAdminTitle.vue'
import { useRequestFailure } from '@/composables/useRequestFailure'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

const page = usePage<InertiaPageProps<{ settings: string }>>()
const { onHttpException, onNetworkError } = useRequestFailure()
const form = useForm({ json: page.props.settings })

// The server confirms the save with a flash message
function submitForm(): void {
  form.put(route('admin.settings.edit'), { preserveScroll: true, onHttpException, onNetworkError })
}
</script>

<template>
  <po-wrapper>
    <po-admin-title :title="$t('admin.settings')" />

    <v-form id="settings-form" class="mb-5" @submit.prevent="submitForm">
      <v-textarea
        v-model="form.json"
        :label="$t('admin.settings')"
        rows="20"
        :hint="$t('admin.settings-warning')"
        hide-details="auto"
        :error-messages="form.errors.json"
        persistent-hint
        required
      />

      <po-button type="submit" color="primary" size="large" block :disabled="form.processing">
        <template v-if="form.processing"><v-progress-circular indeterminate /></template>
        <template v-else>{{ $t('main.save') }}</template>
      </po-button>
    </v-form>
  </po-wrapper>
</template>
