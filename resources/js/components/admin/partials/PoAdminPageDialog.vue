<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'

interface EditablePage {
  slug: string
  title: string
  text: string
}

// Without a page, the dialog creates a new one
const props = defineProps<{
  page: EditablePage | null
}>()

const emit = defineEmits<{
  saved: []
}>()

const TEXT_MIN_LENGTH = 100

const isOpen = defineModel<boolean>({ required: true })
const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError } = useRequestFailure()
const isUpdate = props.page !== null
const form = useForm({
  title: props.page?.title ?? '',
  text: props.page?.text ?? ''
})

// The server returns to the table, confirming with a flash message
function submit(event: Event): void {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  const options = {
    preserveState: true,
    preserveScroll: true,
    onHttpException,
    onNetworkError,
    onSuccess: () => {
      isOpen.value = false
      emit('saved')
    }
  }

  if (props.page !== null) {
    form.put(route('admin.pages.update', props.page.slug), options)
  } else {
    form.post(route('admin.pages.store'), options)
  }
}
</script>

<template>
  <v-dialog v-model="isOpen" width="800" persistent>
    <v-card :title="isUpdate ? $t('pages.edit-page') : $t('pages.create-page')">
      <po-modal-close @click.prevent="isOpen = false" />

      <v-card-text>
        <v-form id="admin-page-form" @submit.prevent="submit">
          <v-text-field
            id="admin-page-title"
            v-model="form.title"
            :label="$t('main.title')"
            :error-messages="form.errors.title"
            minlength="3"
            maxlength="40"
            required
            hide-details="auto"
          />

          <v-textarea
            id="admin-page-text"
            v-model="form.text"
            :label="$t('main.text')"
            :error-messages="form.errors.text"
            :minlength="TEXT_MIN_LENGTH"
            rows="15"
            required
            hide-details="auto"
          />

          <po-button
            id="admin-page-submit"
            color="primary"
            type="submit"
            block
            :disabled="form.processing"
          >
            <span v-if="!form.processing">{{ $t('main.save') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
