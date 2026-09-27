<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3'
import { replyBoxKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'

const props = defineProps<{
  formId: string
  replyTo?: string
}>()

const emit = defineEmits<{
  commentPosted: []
}>()
const writing = injectStrict(writingKey)
const replyBox = injectStrict(replyBoxKey)
const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError, whenSettled } = useRequestFailure()
const request = useHttp({ writing_id: writing.id, comment: props.replyTo ?? '' })

async function submitForm(event: Event): Promise<void> {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  await whenSettled(
    request.post(route('comments.store'), {
      onHttpException,
      onNetworkError,
      onSuccess: () => {
        request.comment = ''
        emit('commentPosted')
        replyBox.value = 0
      }
    })
  )
}
</script>

<template>
  <v-form :id="formId" @submit.prevent="submitForm">
    <v-textarea
      :id="`${formId}-message`"
      v-model="request.comment"
      :label="$t('comments.comment')"
      :placeholder="$t('comments.comment-mention', { at: '@' })"
      rows="3"
      max-length="300"
      hide-details="auto"
      :error-messages="request.errors.comment"
      auto-grow
      clearable
      persistent-placeholder
      required
    />

    <po-button
      :id="`${formId}-submit`"
      color="primary"
      variant="tonal"
      class="mt-1"
      type="submit"
      block
      :disabled="request.processing"
    >
      {{ $t('comments.post-comment') }}
    </po-button>
  </v-form>
</template>
