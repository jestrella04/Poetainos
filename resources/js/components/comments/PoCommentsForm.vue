<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { replyBoxKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useFormValidation } from '@/composables/useFormValidation'
import type { ValidationError } from '@/types/http'

const props = defineProps<{
  formId: string
  replyTo?: string
}>()

const emit = defineEmits<{
  commentPosted: []
}>()
const { checkFormValidity } = useFormValidation()
const writing = injectStrict(writingKey)
const message = ref(props.replyTo)
const errorMessages = ref<string[]>([])
const replyBox = injectStrict(replyBoxKey)

async function submitForm() {
  const form = document.querySelector<HTMLFormElement>(`#${props.formId}`)

  if (!form || !checkFormValidity(form)) {
    return
  }

  errorMessages.value = []

  await axios
    .post(form.action, { writing_id: writing.id, comment: message.value })
    .then(() => {
      message.value = ''
      emit('commentPosted')
      replyBox.value = 0
    })
    .catch((error: ValidationError) => {
      errorMessages.value = error.response?.data.errors.comment ?? []
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
      :error-messages="errorMessages"
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
