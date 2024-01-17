<script setup>
import { inject, ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  formId: { type: String, required: true },
  replyTo: String
})

const emit = defineEmits('commentPosted')
const helper = inject('helper')
const writing = inject('writing')
const message = ref(props.replyTo)
const errorMessages = ref([])
const replyBox = inject('replyBox')

async function submitForm() {
  const form = document.querySelector(`#${props.formId}`)

  if (!helper.checkFormValidity(form)) {
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
    .catch((error) => {
      errorMessages.value = error.response.data.errors.comment
    })
    .finally(() => { })
}
</script>

<template>
  <v-form :id="formId" :action="$route('comments.store')" :data="writing.id" @submit.prevent="submitForm">
    <v-textarea v-model="message" :label="$t('comments.comment')"
      :placeholder="$t('comments.comment-mention', { at: '@' })" rows="2" max-length="300" hide-details="auto"
      :error-messages="errorMessages" auto-grow clearable persistent-placeholder required></v-textarea>
    <po-button type="submit" block class="mt-1">{{ $t('comments.post-comment') }}</po-button>
  </v-form>
</template>