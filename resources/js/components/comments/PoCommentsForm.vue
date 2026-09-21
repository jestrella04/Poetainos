<script setup lang="ts">
import { ref } from 'vue'
import { replyBoxKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useFormErrors } from '@/composables/useFormErrors'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { LaravelValidationErrors } from '@/types/http'

const props = defineProps<{
  formId: string
  replyTo?: string
}>()

const emit = defineEmits<{
  commentPosted: []
}>()
const { validationErrors } = useFormErrors()
const { errors, submitForm: postForm } = useFormSubmit<LaravelValidationErrors>({})
const writing = injectStrict(writingKey)
const message = ref(props.replyTo)
const replyBox = injectStrict(replyBoxKey)

async function submitForm() {
  await postForm({
    formSelector: `#${props.formId}`,
    payload: { writing_id: writing.id, comment: message.value },
    onSuccess: () => {
      message.value = ''
      emit('commentPosted')
      replyBox.value = 0
    },
    onError: validationErrors
  })
}
</script>

<template>
  <v-form
    :id="formId"
    :action="route('comments.store')"
    :data="writing.id"
    @submit.prevent="submitForm"
  >
    <v-textarea
      v-model="message"
      :label="$t('comments.comment')"
      :placeholder="$t('comments.comment-mention', { at: '@' })"
      rows="3"
      max-length="300"
      hide-details="auto"
      :error-messages="errors.comment"
      auto-grow
      clearable
      persistent-placeholder
      required
    />

    <po-button color="primary" variant="tonal" class="mt-1" type="submit" block>{{
      $t('comments.post-comment')
    }}</po-button>
  </v-form>
</template>
