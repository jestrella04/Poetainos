<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useFormValidation } from '@/composables/useFormValidation'
import { usePasswordConfirmation } from '@/composables/usePasswordConfirmation'
import { useRequestFailure } from '@/composables/useRequestFailure'

const props = withDefaults(
  defineProps<{
    url: string
    warningKey: string
    requiresPassword?: boolean
  }>(),
  {
    requiresPassword: false
  }
)

const emit = defineEmits<{
  deleted: []
}>()

const isOpen = defineModel<boolean>({ required: true })
const { isSubmittedFormValid } = useFormValidation()
const { passwordConfirmation, confirmThen } = usePasswordConfirmation()
const { onHttpException, onNetworkError } = useRequestFailure()
const form = useForm({})
const isProcessing = computed(() => passwordConfirmation.processing || form.processing)
// The server refuses some deletions (a category still in use) with a validation error
const refusal = computed(() => Object.values(form.errors)[0] ?? null)

// The server returns to the table, confirming with a flash message
function destroy(): void {
  form.delete(props.url, {
    preserveState: true,
    preserveScroll: true,
    onHttpException,
    onNetworkError,
    onSuccess: () => {
      isOpen.value = false
      emit('deleted')
    }
  })
}

async function submit(event: Event): Promise<void> {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  if (props.requiresPassword === true) {
    await confirmThen(destroy)

    return
  }

  destroy()
}
</script>

<template>
  <v-dialog v-model="isOpen" width="500" persistent>
    <v-card :title="$t('main.proceed-with-caution')">
      <po-modal-close @click.prevent="isOpen = false" />

      <v-card-text>
        <p class="mb-2">
          {{ $t('main.permanent-delete-ask') }}
          {{ $t('main.action-irreversible') }}
        </p>

        <v-alert color="warning" variant="tonal">
          <p>{{ $t(warningKey) }}</p>
        </v-alert>

        <v-alert v-if="refusal !== null" type="error" variant="tonal" class="mt-3">
          {{ refusal }}
        </v-alert>

        <v-divider class="mt-3" />

        <v-form id="admin-delete-form" @submit.prevent="submit">
          <v-text-field
            v-if="requiresPassword"
            id="admin-delete-password"
            v-model="passwordConfirmation.password"
            type="password"
            :label="$t('main.password')"
            :placeholder="$t('main.enter-password-to-continue')"
            :error-messages="passwordConfirmation.errors.password"
            persistent-placeholder
            clearable
            required
            hide-details="auto"
          />

          <po-button
            id="admin-delete-submit"
            color="primary"
            type="submit"
            block
            :disabled="isProcessing"
          >
            <span v-if="!isProcessing">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
