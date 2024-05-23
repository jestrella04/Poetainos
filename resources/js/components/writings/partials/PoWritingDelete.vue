<script setup>
import { ref, inject } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

defineProps({
  slug: { type: String, required: true }
})

const helper = inject('helper')
const isDelete = inject('isDelete')
const isPosting = ref(false)
const errors = ref(false)
const forceSnackBar = inject('forceSnackBar')

async function submit() {
  const form = document.querySelector('#writing-delete-form')

  isPosting.value = true
  errors.value = false

  await axios
    .post(form.action, {
      _method: 'DELETE'
    })
    .then(() => {
      router.visit(route('home'))
      helper.setSnackBar({
        message: 'writings.writing-deleted',
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

        <v-alert color="warning" variant="tonal">
          <p>{{ $t('writings.delete-writing-warning') }}</p>
        </v-alert>

        <v-divider class="mt-3"></v-divider>

        <v-form
          id="writing-delete-form"
          :action="route('writings.destroy', slug)"
          @submit.prevent="submit"
        >
          <po-button color="primary" type="submit" block>
            <span v-if="!isPosting">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate></v-progress-circular>
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
