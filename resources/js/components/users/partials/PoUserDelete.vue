<script setup>
import { ref, inject } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { reactive } from 'vue';

defineProps({
  username: { type: String, required: true },
})

const helper = inject('helper')
const isDelete = inject('isDelete')
const isPosting = ref(false)
const errors = ref(false)
const forceSnackBar = inject('forceSnackBar')
const formData = reactive({
  password: ''
})

async function submit() {
  const form = document.querySelector('#user-delete-form')

  if (!helper.checkFormValidity(form)) {
    return
  }

  isPosting.value = true
  errors.value = false

  await axios
    .post(window.route('password.confirmer'), {
      //'_method': 'DELETE',
      password: formData.password
    })
    .then(() => {
      axios
        .post(form.action, {
          '_method': 'DELETE',
        }).then(() => {
          router.visit(window.route('home'))
          helper.setSnackBar({
            message: 'accounts.account-deleted',
            color: 'success',
            active: true
          })

          forceSnackBar.value = true
          isDelete.value = false
        })
        .catch()
        .finally()
    })
    .catch((error) => {
      errors.value = error.response.data.errors
    })
    .finally(() => {
      isPosting.value = false
    })
}
</script>

<template>
  <v-dialog width="500">
    <v-card :title="$t('accounts.delete-account')">
      <v-card-text>
        <p class="mb-2">
          {{ $t('accounts.sorry-see-you-go') }}
          {{ $t('main.proceed-with-caution') }}
          {{ $t('main.action-irreversible') }}
        </p>

        <v-alert color="warning" variant="tonal">
          <p>{{ $t('accounts.delete-account-warning') }}</p>
        </v-alert>

        <v-divider class="mt-3"></v-divider>

        <v-form id="user-delete-form" :action="$route('users.destroy', username)" @submit.prevent="submit">
          <v-text-field v-model="formData.password" type="password" :label="$t('main.password')"
            :placeholder="$t('main.enter-password-to-continue')" :error-messages="errors.password" persistent-placeholder
            clearable required hide-details="auto">
          </v-text-field>

          <po-button color="primary" type="submit" block :disabled="isPosting">
            <span v-if="!isPosting">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate></v-progress-circular>
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>