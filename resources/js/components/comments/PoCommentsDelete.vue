<script setup>
import { ref, inject } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

defineProps({
  comment: { type: Object, required: true },
})

const helper = inject('helper')
const isDelete = inject('isDelete')
const isPosting = ref(false)
const errors = ref(false)
const forceSnackBar = inject('forceSnackBar')
const writing = inject('writing')

async function submit() {
  const form = document.querySelector('#comment-delete-form')

  isPosting.value = true
  errors.value = false

  await axios
    .post(form.action, {
      '_method': 'DELETE',
    })
    .then(() => {
      router.visit(window.route('writings.show', writing.slug))
      helper.setSnackBar({
        message: 'comments.comment-deleted',
        color: 'success',
        active: true
      })

      forceSnackBar.value = true
      isDelete.value = false
    })
    .catch(() => {
      errors.value = true
    })
    .finally(() => {
      isPosting.value = false
    })
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('main.proceed-with-caution')">
      <po-modal-close @click.prevent="isDelete = false"></po-modal-close>
      <v-card-text>
        <p class="mb-2">
          {{ $t('main.permanent-delete-ask') }}
          {{ $t('main.action-irreversible') }}
        </p>

        <v-divider class="mt-3"></v-divider>

        <v-form id="comment-delete-form" :action="route('comments.destroy', comment.id)" @submit.prevent="submit">
          <po-button color="primary" type="submit" block>
            <span v-if="!isPosting">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate></v-progress-circular>
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>